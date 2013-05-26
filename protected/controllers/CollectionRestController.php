<?php

/**
 * Class CollectionRestController
 * Date: 3/16/13
 * Time: 12:14 AM
 * @author: Spiros Kabasakalis <kabasakalis@gmail.com>
 * @copyright Copyright &copy; Spiros Kabasakalis 2013
 * @license The MIT License  http://opensource.org/licenses/MIT
 */

class CollectionRestController extends ERestController
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
            $modelName = 'Collection';
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
            'Collections Retrieved Successfully',
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
        $images = $data['images'];
        $imageIDs = array();
        foreach ($images as $image) {
            $imageIDs[] = $image['id'];
        }

        $model = $this->loadOneModel($id);
        if (is_null($model)) {
            $this->HTTPStatus = $this->getHttpStatus(404);
            throw new CHttpException(404, 'Collection  Not Found');
        } else {
            if (!(empty($images)))
                $model->images = $imageIDs; else $model->images = array();

            $model->attributes = $data;
            // $model->images = $imageIDs;

            if ($model->save()) {

                header('success', true, 200);
                echo
                json_encode(array(
                        'data' => array(
                            'success' => true,
                            strtolower(get_class($model)) => $model->attributes,
                            'images' => $images
                        )
                    )
                );
                exit;
            } else {
                header('error', true, 400);
                $errors = $model->getErrors();
                echo json_encode(array(
                        'data' => array(
                            'success' => false,
                            'errors' => $errors
                        )
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
            throw new CHttpException(404, 'Collection Not Found');
        } else {
            if ($model->delete())
                // $data = array('success' => true, 'message' => 'Collection Deleted', 'data' => array('id' => $id));
                $data = array('success' => true, 'message' => 'Collection Deleted', 'id' => $id);
            else {
                $this->HTTPStatus = $this->getHttpStatus(406);
                throw new CHttpException(406, 'Could not delete Collection  with ID: ' . $id);
            }
            $this->renderJson(array('data' => $data));
        }
    }


    public function doRestCreate($data)
    {

        $images = $data['images'];
        $imagesResponse = array(); //returned to client to inform about new related images
        $image_ids = array(); //used to assign related images
        foreach ($images as $image) {
            $image_ids[] = $image['id'];
            $img = array();
            $img['id'] = $image['id'];
            $img['name'] = $image['text'];
            $imagesResponse[] = $img;
        }

        $model = new Collection;
        $model->attributes = $data;
        if (!(empty($images)))
            $model->images = $image_ids;
        else $model->images = array();
        //additional afterSave logic in Model file

        if ($model->save()) {

            header('success', true, 200);
            echo
            json_encode(array(
                    'data' => array(
                        'success' => true,
                        strtolower(get_class($model)) => $model->attributes,
                        'images' => $imagesResponse
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