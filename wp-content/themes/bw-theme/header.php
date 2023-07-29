<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <?php wp_head(); ?>

</head>

<body>

    <header class="header">
        <div class="header-container">
            <nav>
                <ul class="menu">
                    <li class="menu__item">
                        <a class="menu__link" href="<?= get_home_url(); ?>">Home</a>
                    </li>
                    <li class="menu__item"><a class="menu__link" href="#about-us">About Us</a></li>
                    <li class="menu__item"><a class="menu__link" href="#team">Team</a></li>
                    <li class="menu__item logo custom-logo logo_header-pos"><?php the_custom_logo(); ?></li>
                    <li class="menu__item"><a class="menu__link" href="#services">Services</a></li>
                    <li class="menu__item"><a class="menu__link" href="#">Blog</a></li>
                    <li class="menu__item"><a class="menu__link" href="#contact-us">Contact Us</a></li>
                </ul>
                <ul class="menu-mobile menu">
                    <li class="menu__item menu-mobile__item">
                        <a class="menu__link" href="<?= get_home_url(); ?>">Home</a>
                    </li>
                    <li class="menu__item menu-mobile__item"><a class="menu__link" href="#about-us">About Us</a></li>
                    <li class="menu__item menu-mobile__item"><a class="menu__link" href="#team">Team</a></li>
                    <li class="menu__item menu-mobile__item"><a class="menu__link" href="#services">Services</a></li>
                    <li class="menu__item menu-mobile__item"><a class="menu__link" href="#">Blog</a></li>
                    <li class="menu__item menu-mobile__item"><a class="menu__link" href="#contact-us">Contact Us</a></li>
                </ul>
                <div class="burger-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>