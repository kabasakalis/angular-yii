//Constants
angular.module('app')
  // .constant('YII_APP_BASE_URL', 'http://yii.gr/yii_backend/')                                       //absolute base  url of your Yii application,trailing slash included.
    .constant('YII_APP_BASE_URL', '/../yii_backend/')                                                   // OR relative base url of your Yii application,trailing slash included.
    .constant('IMAGE_REST_CONTROLLER_ID', 'imageRest')                                  //yii  image controller id
    .constant('COLLECTION_REST_CONTROLLER_ID', 'collectionRest')          //yii collection controller id
    .constant('X_REST_USERNAME', '[YOUR USERNAME]')                                     //username,must match username defined in server side.(protected/congig/main.php)
    .constant('X_REST_PASSWORD', '[YOUR PASSWORD]')                                  //password,must match password defined in server side.(protected/congig/main.php)
    .constant('MAX_FILE_SIZE', 100 * 1024)                                                                   //max file size
    .constant('IMAGES_FOLDER_PATH', 'uploads/images/')                             //upload folder for images,must include a thumbnails subfolder.
                                                                                                                                                               // If you change this,You must also change it on server side,see models/Image.php
    .constant('MAX_FILES_NUMBER', 30)                                                                   //max number of pictures allowed to  upload.
    .constant('MAX_COLLECTIONS_NUMBER', 10)                                            //max number of collections allowed to create








