<?php

/**
 * Class ImageRestController
 * Date: 3/16/13
 * Time: 12:14 AM
 * @author: Spiros Kabasakalis <kabasakalis@gmail.com>
 * @copyright Copyright &copy; Spiros Kabasakalis 2013
 * @license The MIT License  http://opensource.org/licenses/MIT
 */

class ImageRestController extends ERestController
{


    /**
     * Returns the model assosiated with this controller.
     * The assumption is that the model name matches your controller name
     * If this is not the case you should override this method in your controller
     */
    public function getModel()
    {
        if ($this->model === null) {
            /*	$modelName = str_replace('Controller', '', get_class($this));*/
            $modelName = 'Image';
            $this->model = new $modelName;
        }
        $this->_attachBehaviors($this->model);
        return $this->model;
    }

    /**
     * Helper for loading a single model
     */
    protected function loadOneModel($id)
    {
        return $this->getModel()->with($this->nestedRelations)->findByPk($id);
    }


    /**
     * This is broken out as a sperate method from actionRestList
     * To allow for easy overriding in the controller
     * and to allow for easy unit testing
     */
    public function doRestList()
    {
        $this->outputHelper(
            'Images Retrieved Successfully',
            $this->getModel()->with($this->nestedRelations)
                ->filter($this->restFilter)->orderBy($this->restSort)
                ->limit($this->restLimit)->offset($this->restOffset)
                ->findAll(),
            $this->getModel()
                ->with($this->nestedRelations)
                ->filter($this->restFilter)
                ->count()
        );
    }


    public function outputHelper($message, $results, $totalCount = 0, $model = null)
    {
        if (is_null($model))
            $model = lcfirst(get_class($this->model));
        else
            $model = lcfirst($model);

        $this->renderJson(array(
            //'success'=>true,
            //'message'=>$message,
            'data' => array(
                'success' => true,
                'totalCount' => $totalCount,
                'message' => $message,
                $model => $this->allToArray($results)
            )
        ));
    }


    /**
     * This is broken out as a sperate method from actionResUpdate
     * To allow for easy overriding in the controller
     * and to allow for easy unit testing
     */
    public function doRestUpdate($id, $data)
    {
        $collections = $data['collections'];
        $collectionIDs = array();
        foreach ($collections as $collection) {
            $collectionIDs[] = $collection['id'];
        }

        $model = $this->loadOneModel($id);
        if (is_null($model)) {
            $this->HTTPStatus = $this->getHttpStatus(404);
            throw new CHttpException(404, 'Image  Not Found');
        } else {
            if (!(empty($collections)))
                $model->collections = $collectionIDs;
            //   else    $model->collections = array(Collection::WAREHOUSE_ID);
            else    $model->collections = array();
            $model->attributes = $data;
            //$model->collections =$categoryIDs;

            if ($model->save()) {

                header('success', true, 200);
                echo
                json_encode(array(
                        'success' => true,
                        strtolower(get_class($model)) => $model->attributes,
                        'collections' => $collections
                    )
                );
                exit;
            } else {
                header('error', true, 400);
                $errors = $model->getErrors();
                echo json_encode(array(
                        'success' => false,
                        'errors' => $errors
                    )
                );
                exit;
            }

        }
    }


    public function doRestDelete($id)
    {
        $model = $this->loadOneModel($id);
        if (is_null($model)) {
            $this->HTTPStatus = $this->getHttpStatus(404);
            throw new CHttpException(404, 'Image Not Found');
        } else {
            if ($model->delete())
                // $data = array('success' => true, 'message' => 'Image Deleted', 'data' => array('id' => $id));
                $data = array('success' => true, 'message' => 'Image Deleted', 'id' => $id);
            else {
                $this->HTTPStatus = $this->getHttpStatus(406);
                throw new CHttpException(406, 'Could not delete Image  with ID: ' . $id);
            }
            $this->renderJson(array('data' => $data));
        }
    }


    public function doRestCreate($data)
    {

        /*       dumpvar($data);
             dumpvar($_POST);exit;*/

        $isUpdate = (!empty($_POST['id'])) ? true : false;

        if (isset($_POST)) {
            $collections = explode(',', $_POST['collections']);
            $collections_are_empty = empty($_POST['collections']);
            if ($isUpdate) array_shift($collections); //workaround a select2 bug which sets the first selected item as  an unwanted [object Object]

            if ($isUpdate) {
                $model = $this->loadOneModel($_POST['id']);
            } else $model = new Image;

            //delete old image if we are updating
            if ($isUpdate && !empty($_POST['image'])) {
                @unlink($model->getFullImageFolderPath() . $model->getImageName('full'));
                @unlink($model->getThumbImageFolderPath() . $model->getImageName('thumb'));
            }


            $model->attributes = (!empty($_POST)) ? $_POST : $data;


            $model->image = CUploadedFile::getInstanceByName('image');

            if (isset($model->image)) {
                $model->img_name = slugify(file_ext_strip($model->image->name));
                $model->img_size = $model->image->size;
                $model->img_ext = $model->image->extensionName;
            }

            if (!$collections_are_empty)
                $model->collections = $collections;
            else {

                $model->collections = array();
            }
            //additional afterSave logic in Model file
            if ($model->save()) {

                header('success', true, 200);
                echo
                json_encode(array(
                        'data' => array(
                            'success' => true,
                            strtolower(get_class($model)) => $model->attributes,
                            'collections' => $model->collections
                        )
                    )
                );
                exit;
            } else {
                header('error', true, 400);
                $errors = $model->getErrors();
                echo json_encode(array(

                        'success' => false,
                        'message' => $errors,
                        'errorCode' => '400'
                    )
                );
                exit;
            }
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }


    public function onException($event)
    {
        if (!$this->developmentFlag && ($event->exception->statusCode == 500 || is_null($event->exception->statusCode)))
            $message = "Internal Server Error";
        else {
            $message = $event->exception->getMessage();
            if ($tempMessage = CJSON::decode($message))
                $message = $tempMessage;
        }

        $errorCode = (!isset($event->exception->statusCode) || is_null($event->exception->statusCode)) ? 500 : $event->exception->statusCode;

        $this->renderJson(array('errorCode' => $errorCode, 'message' => $message, 'success' => false));
        $event->handled = true;
        header('error', true, $errorCode);
    }


}