<?php get_header(); ?>
<main class="main">
    <section class="hero">
        <div class="hero-container">
            <h1 class="hero__title"><?= CFS()->get('hero_title'); ?></h1>
            <p class="hero__subtitle"><?= CFS()->get('hero_slogan'); ?></p>
            <button class="hero__btn" onclick="document.querySelector('#about-us').scrollIntoView()">
                <?= CFS()->get('hero_button'); ?>
            </button>
        </div>
    </section>
    <section id="about-us" class="about-us" style="background-image:url(<?= CFS()->get('background'); ?>)">
        <div class="about-us-container">
            <?php
            $loop = CFS()->get('cards');
            foreach ($loop as $row) {
            ?>
                <div class="about-us-card">
                    <h2 class="about-us-card__title"><?= $row['card_year'] ?></h2>
                    <p class="about-us-card__text"><?= $row['card_text'] ?></p>
                </div>
            <?php
            }
            ?>
        </div>
    </section>
    <section id="team" class="team">
        <div class="cards-container">
            <h3 class="section-title"><?= CFS()->get('team_title'); ?></h3>
            <p class="section-subtitle"><?= CFS()->get('team_description'); ?></p>
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php
                    $loop = CFS()->get('team_card');
                    foreach ($loop as $row) {
                    ?>
                        <div class="swiper-slide swiper-slide_centered">
                            <div class="card-team">
                                <div class="card-team__img">
                                    <img 
                                        src="<?= $row['team_image']; ?>" 
                                        width="170" 
                                        height="170" 
                                        alt="photo"
                                        loading="lazy"
                                    >
                                </div>
                                <div class="card-team__content">
                                    <h4 class="card-team__title"><?= $row['team_name']; ?></h4>
                                    <p class="card-team__text"><?= $row['team_position']; ?></p>
                                </div>
                                <div class="card-team__icons">
                                    <?php
                                    if ($row['team_twitter']['url']) {
                                    ?>
                                        <a href="<?= $row['team_twitter']['url']; ?>">
                                            <img 
                                                src="<?= $row['icon1']; ?>"
                                                width="20" 
                                                height="20"
                                                alt="<?= CFS()->get('alt_img1'); ?>"
                                                loading="lazy"
                                            >
                                        </a>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if ($row['team_instagram']['url']) {
                                    ?>
                                        <a href="<?= $row['team_instagram']['url']; ?>">
                                            <img 
                                                src="<?= $row['icon2']; ?>"
                                                width="20" 
                                                height="20"
                                                alt="<?= CFS()->get('alt_img2'); ?>"
                                                loading="lazy"
                                            >
                                        </a>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if ($row['team_vk']['url']) {
                                    ?>
                                        <a href="<?= $row['team_vk']['url'] ?>">
                                            <img 
                                                src="<?= $row['icon3']; ?>"
                                                width="20" 
                                                height="20"
                                                alt="<?= CFS()->get('alt_img3'); ?>"
                                                loading="lazy"
                                            >
                                        </a>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if ($row['team_facebook']['url']) {
                                    ?>
                                        <a href="<?= $row['team_facebook']['url']; ?>">
                                            <img 
                                                src="<?= $row['icon4']; ?>"
                                                width="20" 
                                                height="20"
                                                alt="<?= CFS()->get('alt_img4'); ?>"
                                                loading="lazy"
                                            >
                                        </a>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
    <section id="services" class="services">
        <div class="cards-container">
            <h3 class="section-title"><?= CFS()->get('services_title'); ?></h3>
            <p class="section-subtitle"><?= CFS()->get('services_description'); ?></p>
            <ul class="cards-list">
                <?php
                $loop = CFS()->get('services_cards');
                foreach ($loop as $row) {
                ?>
                    <li class="card-services">
                        <div class="card-services__img">
                            <img 
                                src="<?= $row['services_card_image']; ?>"
                                width="64" 
                                height="54"
                                alt="<?= $row['alt_img']; ?>"                                
                                loading="lazy"
                            >
                        </div>
                        <div class="card-services__content">
                            <h4 class="card-services__title"><?= $row['services_card_title']; ?></h4>
                            <p class="card-services__text"><?= $row['services_card_description']; ?></p>
                        </div>
                    </li>
                <?php
                }
                ?>
            </ul>
        </div>
    </section>
    <section id="contact-us" class="contact-us">
        <div class="contact-us-container">
            <h3 class="section-title"><?= CFS()->get('contact_us_title'); ?></h3>
            <p class="section-subtitle"><?= CFS()->get('contact_us_slogan'); ?></p>
            <div class="contact-cards">
                <div class="contact-card">
                    <img 
                        src="<?php bloginfo('template_url') ?>/assets/images/phone.svg"                         
                        class="contact-card__icon" 
                        width="14" 
                        height="21"
                        alt="phone icon"
                        loading="lazy"
                    >
                    <div class="contact-card__text">
                        <a href="tel:<?= CFS()->get('contact_us_phone'); ?>"><?= CFS()->get('contact_us_phone'); ?></a>
                    </div>
                </div>
                <div class="contact-card">
                    <img 
                        src="<?php bloginfo('template_url') ?>/assets/images/pin.svg"                          
                        width="16" 
                        height="21"
                        loading="lazy"
                        alt="pin icon" class="contact-card__icon"
                    >
                    <div class="contact-card__text">
                        <?php
                        if (CFS()->get('contact_us_address')['url']) {
                        ?>
                            <a 
                                href="<?= CFS()->get('contact_us_address')['url'] ?>"
                                target="<?= CFS()->get('contact_us_address')['target'] ?>">
                                <?= CFS()->get('contact_us_address')['text'] ?>
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="contact-card">
                    <img 
                        src="<?php bloginfo('template_url') ?>/assets/images/mail.svg"                          
                        width="19" 
                        height="14"
                        alt="mail icon" class="contact-card__icon contact-card__icon_last"
                        loading="lazy"
                    >
                    <div class="contact-card__text">
                        <a href="mailto:<?= CFS()->get('contact_us_email'); ?>"><?= CFS()->get('contact_us_email'); ?></a>
                    </div>
                </div>
            </div>
            
            <?php the_content(); ?>
            
        </div>
    </section>
</main>
<?php get_footer(); ?>