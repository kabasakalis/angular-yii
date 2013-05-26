<?php
/**
 * Class Collection
 * Date: 3/16/13
 * Time: 12:14 AM
 * This is   model class for table "collection".
 * The followings are the available columns in table 'collection':
 * @property string $id
 * @property string $name
 * @property string $description
 * @author: Spiros Kabasakalis <kabasakalis@gmail.com>
 * @copyright Copyright &copy; Spiros Kabasakalis 2013
 * @license The MIT License  http://opensource.org/licenses/MIT
 */

class Collection extends CActiveRecord
{

    const WAREHOUSE_ID = 1;

    /**
     * Returns the static model of the specified AR class.
     * @return Categorydemo the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the class name
     */
    public static function className()
    {
        return __CLASS__;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'collection';
    }

    public function relations()
    {
        return array(
            'images' => array(self::MANY_MANY, 'Image', 'collection_image(collection_id, image_id)'),
        );
    }


    public function behaviors()
    {
        return array(
            'activerecord-relation' => array(
                'class' => 'application.behaviors.ar_relation.EActiveRecordRelationBehavior',
            )
        );
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
            array('name', 'length', 'max' => 128),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('name, description', 'safe'),
            //array('name, description', 'safe', 'on'=>'search'),
        );
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'name' => 'Name',
            'description' => 'Description',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }


    /**
     * Remove (unlink) all images from this collection does not delete images)
     *
     * @param int $image_ids the image ids
     * @return bool
     */
    public function  removeImages(array $image_ids = null)
    {
        try {
            $image_ids_string = join(',', $image_ids);
            $sql = "DELETE FROM collection_image WHERE collection_id= :collection_id";
            if (!is_null($image_ids)) {
                $sql .= " AND image_id  IN ({$image_ids_string})";
            }
            $command = $this->getDbConnection()->createCommand($sql);
            $command->bindValues(array(
                ":collection_id" => $this->primaryKey,
            ));
            $command->execute();

        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }


    /**
     * Returns options for the related model multiple select
     * @param string $model
     * @return  string options for relate model  multiple select
     * @since 1.0
     */


    /**
     * Attach an  image  to thiscollection
     *
     * @todo maybe we should execute bulk insert of links ? It faster a lot
     * @param  int $imageID
     * @return bool
     */
    public function attachImage($imageID)
    {

        try {
            $sql = "INSERT INTO collection_image  (collection_id, image_id) VALUES (:collection_id,:image_id)";
            $command = $this->getDbConnection()->createCommand($sql);
            $command->bindValues(array(
                ":image_id" => $imageID,
                ":collection_id" => $this->primaryKey,
            ));
            $command->execute();
        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return true;
    }


    /**
     * Attach Images  to collection
     *
     * @todo maybe we should execute bulk insert of links,faster?
     * @param array $imageIDS the image primary keys we want to attach to this category
     * @return bool $success
     */
    public function attachImages(array $imageIDS)
    {
        $success = true;
        try {
            foreach ($imageIDS as $imageID) {
                $success = $success && $this->attachImage($imageID);
            }
        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
        return $success;
    }


    public function image_select_options()
    {

        $sql_related_images = "SELECT  image_id  FROM collection_image   WHERE collection_id=:collection_id";
        $command_related_images = $this->getDbConnection()->createCommand($sql_related_images);
        $command_related_images->bindValues(array(
            ":collection_id" => $this->id,
        ));
        $related_images = $command_related_images->queryAll(false);
        $related_image_ids = array();
        foreach ($related_images as $image) {
            $related_image_ids[] = $image[0];
        }

        $sql = "SELECT id,name FROM image";
        $command = $this->getDbConnection()->createCommand($sql);
        $images = $command->queryAll();
        //  var_dump($categories);
        //var_dump($related_collection_ids);exit;
        $options = '';
        foreach ($images as $image) {
            if (!$this->isNewRecord) {
                in_array($image['id'], $related_image_ids) ?
                    $options .= '<option selected="selected" value=' . $image['id'] . '>' . $image['name'] . '</option>\n' :
                    $options .= '<option  value=' . $image['id'] . '>' . $image['name'] . '</option>\n';
            } else {
                $options .= '<option  value=' . $image['id'] . '>' . $image['name'] . '</option>\n';
            }
        }
        return $options;
    }


}