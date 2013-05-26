<?php $this->beginContent('//layouts/main'); ?>
<div id="wrapper" class="container">
    <header id="mainheader" class="ir">
        <div id="topmenu">
                    <ul>
                          <li  id="loggedin_user" title="<?php echo t('login', 'Signed In'); ?>">
                              <?php if(Yii::app()->user->id) echo Yii::app()->user->name; ?></li>
                             <li><a id="ajax_logout" class="ttip_b hidden" href="<?php echo bu() . '/site/logout' ?>"
                               title="<?php echo t('login', 'Log Out'); ?>"><?php echo t('login', 'Log Out'); ?></a></li>
                        <?php if (!Yii::app()->user->id): ?>
                        <li><a   href="<?php echo bu() . '/site/register' ?>"
                               title="<?php echo t('register', 'Sign Up'); ?>"><?php echo t('register', 'Sign Up'); ?> </a></li>
                        <li><a   href="<?php echo bu() . '/site/login' ?>"
                               title="<?php echo t('login', 'Sign In'); ?>"><?php echo t('login', 'Sign In'); ?></a></li>
                        <?php endif;?>
                         <?php if (Yii::app()->user->id): ?>
                        <li><a  href="<?php echo bu() . '/site/logout' ?>"
                               title="<?php echo t('login', 'Log Out'); ?>"><?php echo t('login', 'Log Out'); ?></a></li>
                           <?php endif;?>
                    </ul>
                </div>

    </header>
    <nav id="main-nav" role="navigation">

        <!-- Bootstrap Navigation Bar Menu -->
        <div class="navbar navbar-inverse" >
            <div class="navbar-inner">
                <div class="container">
                    <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>

                    <!-- Be sure to leave the brand out there if you want it shown -->
                    <a class="brand" href="#">myYii Backend</a>

                    <div class="nav-collapse">
                        <?php $this->widget('zii.widgets.CMenu', array(
                                                                      'items' => array(
                                                                          array('label' => 'Home', 'url' => array('/site/index')),
                                                                          array('label' => 'About', 'url' => array('/site/page', 'view' => 'about')),
                                                                          array('label' => 'Contact', 'url' => array('/site/contact')),
                                                                         array('label' => 'Register', 'url' => array('/site/register')),
                                                                          array('label' => 'Login', 'url' => array('/site/login'), 'visible' => Yii::app()->user->isGuest),
                                                                          array('label' => 'Logout (' . (Yii::app()->user->name) . ')', 'url' => array('/site/logout'), 'visible' => !Yii::app()->user->isGuest)
                                                                      ),
                                                                      // 'htmlOptions'=>array('class'=>'main-menu')
                                                                      'htmlOptions' => array('class' => 'nav')
                                                                 )); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap Navigation Bar Menu -->
    </nav>

    <div class="row">
        <div class='span9'>
            <?php $this->widget('zii.widgets.CBreadcrumbs', array(
                                                                 'links' => $this->breadcrumbs,
                                                            ));?>
        </div>

        <div class='span9'>
            <?php
            $flashMessages = Yii::app()->user->getFlashes();
            if ($flashMessages) {
                echo '<div class="flash-messages">';
                foreach ($flashMessages as $key => $message) {
                    echo '<div class="alert alert-' . $key . '">' . "
          <a class='close' data-dismiss='alert'>×</a>
       {$message}
       </div>\n";
                }
                echo '</div>';
            }
            ?>
        </div>
        <div id="main" class="span9" role="main">

            <?php echo $content; ?>
        </div>
        <!-- main content -->
        <aside id="sidebar" class='span3'>
            <h2>Sidebar</h2>

            <p>
                <img id="sidebar-img" alt='sidebar'
                     src='http://placehold.it/150x150.png/555555/777777&amp;text=Sidebar'>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Praesent sit amet libero eros. Vivamus convallis, libero eu iaculis cursus, diam dui ultrices lorem, a
                ornare quam orci quis libero.
                Morbi interdum cursus odio, sed tincidunt eros tristique sit amet. Mauris sollicitudin diam eget ipsum
                blandit molestie.
                Praesent eu arcu odio. Nulla et adipiscing augue. Donec facilisis ante ac risus dignissim et interdum
                orci
                porttitor.
                Vivamus posuere blandit venenatis. Proin purus nibh, fringilla id dictum id, eleifend sed diam.
                Pellentesque ac sapien non ipsum blandit tincidunt. Aenean ullamcorper rutrum lacus ac molestie.
                Aliquam sed sem massa. Etiam in rutrum nisl. Morbi lobortis fermentum tellus, vel pharetra purus
                tincidunt
                venenatis.
                Ut lectus nulla, aliquet a scelerisque vel, cursus congue metus. Proin eget est mi, sed commodo nulla.
                Sed porta ornare nisl at viverra. Morbi quis quam egestas orci pellentesque tempus consectetur congue
                justo.
            </p>
        </aside>
        <!-- sidebar -->
        <footer class='span12'>
            <div class="row">
            <div  class='span8'>
                Copyright &copy; <?php echo date('Y'); ?> by Spiros Kabasakalis.<br/>
                All Rights Reserved.<br/> <?php echo Yii::powered(); ?>Built with <a
                    href="http://html5boilerplate.com/">HTML5 Boilerplate</a>,
                <a href="http://twitter.github.com/bootstrap/index.html">Bootstrap</a>,
                <a href="http://yii-booster.clevertech.biz/">YiiBooster</a> and
                 <a href="http://bootswatch.com/">Bootswatch</a>.
            </div>
            <a href="http://www.w3.org/html/logo/" class="span1">
               <img src="http://www.w3.org/html/logo/badge/html5-badge-h-solo.png" width="63" height="64" alt="HTML5 Powered" title="HTML5 Powered">
               </a>
                </div>
        </footer>
    </div>
    <!-- row-->

</div>     <!-- wrapper -->
<?php $this->endContent(); ?>