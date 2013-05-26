<?php

class m121020_230247_user extends CDbMigration
{
    public function up()
    {
        $this->execute("
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 0,
  `password` varchar(255) DEFAULT NULL,
  `password_strategy` varchar(50) DEFAULT NULL,
   `salt` varchar(255) DEFAULT NULL,
  `requires_new_password` tinyint(1) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT NULL,
  `login_time` int(11) DEFAULT NULL,
  `login_ip` varchar(32) DEFAULT NULL,
  `activation_key` varchar(128) DEFAULT NULL,
  `validation_key` varchar(255) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;
      		 ");

        /* add demo users */
        $demoUser = new User();
        $demoUser->username = "demo";
        $demoUser->email = "demo@demo.com";
        $demoUser->password=1;
        $demoUser->status = 1;


        $demoUser->save();

        $adminUser = new User();
        $adminUser->username = "admin";
        $adminUser->email = "admin@admin.com";
        $adminUser->password =1;
        $adminUser->status=1;


        $adminUser->save();

    }

    public function down()
    {
        $this->dropTable(`user`);
    }

    /*
    // Use safeUp/safeDown to do migration with transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}