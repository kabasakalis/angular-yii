<?php
/**
 * Class Image
 * Date: 3/16/13
 * Time: 12:14 AM
 * This is   model class for table "image".
 * The followings are the available columns in table 'collection':
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $price
 * @author: Spiros Kabasakalis <kabasakalis@gmail.com>
 * @copyright Copyright &copy; Spiros Kabasakalis 2013
 * @license The MIT License  http://opensource.org/licenses/MIT
 */


class Image extends CActiveRecord
{
    public $image;

    const  UPLOADS_FOLDER = 'uploads';
    const FULL_IMG_FOLDER = 'images';
    const THUMB_IMG_FOLDER = 'thumbnails';
    const IMG_SIZE_LIMIT = 256000; //250*1024 KB

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Pro the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'image';
    }


    public static function getFullImageFolderPath()
    {
        return Yii::getPathOfAlias('webroot') . DS . self::UPLOADS_FOLDER . DS . self::FULL_IMG_FOLDER . DS;
    }

    public static function getThumbImageFolderPath()
    {
        return Yii::getPathOfAlias('webroot') . DS . self::UPLOADS_FOLDER . DS . self::FULL_IMG_FOLDER . DS . self::THUMB_IMG_FOLDER . DS;
    }


    public function createThumb($width, $height, $adaptive = true, $extension = null)
    {
        $id = ($this->primaryKey) ? $this->primaryKey : Yii::app()->db->getLastInsertId();

        $thumb = Yii::app()->phpThumb->create($this->image->tempName);

        if ($adaptive)
            $thumb->adaptiveResize($width, $height);
        else $thumb->resize($width, $height);
        $thumbpath = self::getThumbImageFolderPath() . $this->img_name . '_thumb_' . $id . '.' . $this->img_ext;
        $thumb->save($thumbpath, $extension);
        return array('path' => $thumbpath, 'url' => Yii::app()->baseUrl . '/' . self::UPLOADS_FOLDER . '/' . self::FULL_IMG_FOLDER . '/' . self::THUMB_IMG_FOLDER . '/' . $this->img_name . '_thumb_' . $id . '.' . $this->img_ext);
    }

    public function getImageUrl($type = 'full')
    {
        if (!empty($this->img_name)) {
            switch ($type) {
                case 'full':
                    return Yii::app()->baseUrl . '/' . self::UPLOADS_FOLDER . '/' . self::FULL_IMG_FOLDER . '/' . $this->getImageName('full');
                    break;
                case 'thumb':
                    return Yii::app()->baseUrl . '/' . self::UPLOADS_FOLDER . '/' . self::FULL_IMG_FOLDER . '/' . self::THUMB_IMG_FOLDER . '/' . $this->getImageName('thumb');
                    break;
            }
        } else return ' http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=no+image';
    }


    public function getImageName($type)
    {
        switch ($type) {
            case 'full':
                return $this->img_name . '_full_' . $this->primaryKey . '.' . $this->img_ext;
                break;
            case 'thumb':
                return $this->img_name . '_thumb_' . $this->primaryKey . '.' . $this->img_ext;
                break;
        }

    }


    protected function afterDelete()
    {
        @unlink($this->getFullImageFolderPath() . $this->getImageName('full'));
        @unlink($this->getThumbImageFolderPath() . $this->getImageName('thumb'));

    }


    protected function afterSave()
    {
        $isUpdate = (!empty($_POST['id']) || empty($_POST)) ? true : false;


        if ($isUpdate) {
            $id = $this->primaryKey;
        } else {
            $id = Yii::app()->db->getLastInsertId();
            $this->id = $id;
        }

        if (isset($this->image)) {


            $thumb = $this->createThumb(100, 100);
            $thumb_saved = file_exists($thumb['path']);
            $full_saved = $this->image->saveAs(Image::getFullImageFolderPath() . $this->img_name . '_full_' . $id . '.' . $this->img_ext);

            if (!$thumb_saved || !$full_saved) {
                echo
                json_encode(array(
                        'success' => false,
                        'errors' => array('file_error' => 'Image and/orr thumbnail not saved'),
                    )
                );
                Yii::app()->end();
            }
        }

        parent::afterSave();

    }


    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'collections' => array(self::MANY_MANY, 'Collection', 'collection_image(image_id, collection_id)'),
        );
    }


    public function behaviors()
    {
        return array(
            'activerecord_relation' => array(
                'class' => 'application.behaviors.ar_relation.EActiveRecordRelationBehavior',
            ));
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'required'),
            array('name', 'length', 'max' => 100),
            array('price', 'match', 'pattern' => '/^[0-9]{1,3}(\.[0-9]{0,2})?$/', 'message' => 'Price must be decimal with integer part  length of 3 at most and  decimal  precision of 2.'),
            array('name,price,description,img_name,img_size,id,img_ext', 'safe'),
            array('image', 'file', 'types' => 'jpg, gif, png', 'allowEmpty' => true, 'maxSize' => 256000, 'tooLarge' => 'The file was larger than 250K. Please upload a smaller file.'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, description, price', 'safe', 'on' => 'search'),
        );
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'price' => 'Price',
        );
    }




    public function search($pagination)
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $criteria = new CDbCriteria;
        $criteria->compare('t.id', $this->id, true);
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.description', $this->description, true);
        $criteria->compare('t.price', $this->price, true);

        $image_criteria = new CDbCriteria;

        if (!empty($_GET['cat_id'])) {
            $image_criteria->with = array('collections' => array(
                'on' => 'collection_id=:cat_id',
                'together' => true,
                'joinType' => 'INNER JOIN',
                'params' => array(':cat_id' => $_GET['cat_id'])
            ));
            $criteria->mergeWith($image_criteria);
        }


        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pagination,
            ),
        ));
    }


    /**
     * Remove (unlink) all images  from this collection  (does not delete images)
     *
     * @param int $category_ids the category ids
     * @return bool
     */
    public function  unlink_collections(array $collection_ids = null)
    {
        try {
            $collection_ids_string = join(',', $collection_ids);
            $sql = "DELETE FROM  collection_image WHERE image_id= :image_id";
            if (!is_null($collection_ids)) {
                $sql .= " AND collection_id  IN ({$collection_ids_string})";
            }
            $command = $this->getDbConnection()->createCommand($sql);
            $command->bindValues(array(
                ":image_id" => $this->primaryKey,
            ));
            $command->execute();

        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }


    /**
     * Insert link between parent and relation models into database
     *
     * @todo maybe we should execute bulk insert of links ? It faster a lot
     * @param $collectionID
     * @return bool
     */
    public function attachToCollection(int $collectionID)
    {
        try {
            $sql = "INSERT INTO collection_image  (collection_id, image_id) VALUES (:collection_id,:image_id)";
            $command = $this->model->getDbConnection()->createCommand($sql);
            $command->bindValues(array(
                ":image_id" => $this->primaryKey,
                ":collection_id" => $collectionID,
            ));
            $command->execute();
        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }

    /**
     * Attach collections  to this image  instance
     * @todo maybe we should execute bulk insert of links ? It faster a lot
     * @param $collectionIDs
     * @return bool
     */
    public function attachToCollections($collectionIDs)
    {
        $success = true;
        try {
            foreach ($collectionIDs as $collectionID) {
                $success = $success && $this->attachToCategory($collectionID);
            }
        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return $success;
    }


    public function collection_select_options()
    {

        $sql_related_collections = "SELECT  collection_id  FROM  collection_image   WHERE  image_id=:image_id";
        $command_related_collections = $this->getDbConnection()->createCommand($sql_related_collections);
        $command_related_collections->bindValues(array(
            ":image_id" => $this->id,
        ));
        $related_collections = $command_related_collections->queryAll(false);
        $related_collection_ids = array();
        foreach ($related_collections as $collection) {
            $related_collection_ids[] = $collection[0];
        }

        $sql = "SELECT id,name FROM collection";
        $command = $this->getDbConnection()->createCommand($sql);
        $collections = $command->queryAll();
        //  var_dump($categories);
        //var_dump($related_category_ids);exit;
        $options = '';
        foreach ($collections as $collection) {
            if (!$this->isNewRecord) {
                in_array($collection['id'], $related_collection_ids) ?
                    $options .= '<option selected="selected" value=' . $collection['id'] . '>' . $collection['name'] . '</option>\n' :
                    $options .= '<option  value=' . $collection['id'] . '>' . $collection['name'] . '</option>\n';
            } else {
                $options .= '<option  value=' . $collection['id'] . '>' . $collection['name'] . '</option>\n';
            }
        }
        return $options;
    }

}