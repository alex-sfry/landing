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
            <h3 class="section-title">This is our team</h3>
            <p class="section-subtitle">We are small but effective and ...</p>
            <ul class="cards-list">
                <li class="card-team">
                    <div class="card-team__img">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/mark_3197.png" alt="Mark Once photo" width="170" height="170">
                    </div>
                    <div class="card-team__content">
                        <h4 class="card-team__title">Mark Once</h4>
                        <p class="card-team__text">Designer & Front-End Developer</p>
                    </div>
                    <div class="card-team__icons">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/twitter.svg" alt="twitter">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/instagram.svg" alt="instagram">
                    </div>
                </li>
                <li class="card-team">
                    <div class="card-team__img">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/justin_3478.png" alt="Justin Twice photo" width="170" height="170">
                    </div>
                    <div class="card-team__content">
                        <h4 class="card-team__title">Justin Twice</h4>
                        <p class="card-team__text">Founder & CEO</p>
                    </div>
                    <div class="card-team__icons">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/twitter.svg" alt="twitter">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/instagram.svg" alt="instagram">
                    </div>
                </li>
                <li class="card-team">
                    <div class="card-team__img">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/antonio_3479.png" alt="Antonio Never photo" width="170" height="170">
                    </div>
                    <div class="card-team__content">
                        <h4 class="card-team__title">Antonio Never</h4>
                        <p class="card-team__text">Someone & Somewhere</p>
                    </div>
                    <div class="card-team__icons">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/twitter.svg" alt="twitter">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/instagram.svg" alt="instagram">
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <section id="services" class="services">
        <div class="cards-container">
            <h3 class="section-title">We provide you everything</h3>
            <p class="section-subtitle">
                Maybe not everything, but we provide you some stuff.
            </p>
            <ul class="cards-list">
                <li class="card-services">
                    <div class="card-services__img">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/line_graph.svg" alt="line graph icon" width="64" height="54">
                    </div>
                    <div class="card-services__content">
                        <h4 class="card-services__title">Some Analytics</h4>
                        <p class="card-services__text">
                            Aenean nisi lectus, convallis non lorem sit amet, rhoncus malesuada justo
                        </p>
                    </div>
                </li>
                <li class="card-services">
                    <div class="card-services__img">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/heart.svg" alt="heart icon" width="73" height="63">
                    </div>
                    <div class="card-services__content">
                        <h4 class="card-services__title">We provide you love</h4>
                        <p class="card-services__text">
                            Aenean nisi lectus, convallis non lorem sit amet, rhoncus malesuada justo
                        </p>
                    </div>
                </li>
                <li class="card-services">
                    <div class="card-services__img">
                        <img src="<?php bloginfo('template_url') ?>/assets/images/upload.svg" alt="upload icon" width="64" height="55">
                    </div>
                    <div class="card-services__content">
                        <h4 class="card-services__title">And Some Cloud</h4>
                        <p class="card-services__text">
                            Aenean nisi lectus, convallis non lorem sit amet, rhoncus malesuada justo
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <section id="contact-us" class="contact-us">
        <div class="contact-us-container">
            <h3 class="section-title">Contac Us</h3>
            <p class="section-subtitle">We know what we need to do</p>
            <div class="contact-cards">
                <div class="contact-card">
                    <img src="<?php bloginfo('template_url') ?>/assets/images/phone.svg" alt="phone icon" class="contact-card__icon" width="14" height="21">
                    <p class="contact-card__text">555-222-333</p>
                </div>
                <div class="contact-card">
                    <img src="<?php bloginfo('template_url') ?>/assets/images/pin.svg" alt="pin icon" class="contact-card__icon" width="16" height="21">
                    <p class="contact-card__text">Here is some address</p>
                </div>
                <div class="contact-card">
                    <img src="<?php bloginfo('template_url') ?>/assets/images/mail.svg" alt="mail icon" class="contact-card__icon contact-card__icon_last" width="19" height="14">
                    <p class="contact-card__text">somemail@hotmail.com</p>
                </div>
            </div>
            <form action="" class="contact-form contact-form_pos">
                <div class="form-controls">
                    <input name="name" type="text" placeholder="Full Name" id="name" class="input form-controls__input" required>
                    <input name="email" type="email" placeholder="Email" id="email" class="input form-controls__input" required>
                    <input name="phone" type="tel" placeholder="Number" id="phone" class="input form-controls__input">
                    <textarea name="txtArea" id="txtArea" class="form-controls__txt-area" placeholder="Write your Message here..."></textarea>
                    <button type="submit" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </section>
</main>
<?php get_footer(); ?>