import gulp from 'gulp';
import { deleteAsync } from 'del';
import dartSass from 'sass';
import gulpSass from 'gulp-sass';
import cleanCss from 'gulp-clean-css';
import rename from 'gulp-rename';
import * as rollup from 'rollup';
import terser from '@rollup/plugin-terser';
import resolve from '@rollup/plugin-node-resolve';
import babel from '@rollup/plugin-babel';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'gulp-autoprefixer';
import imagemin from 'gulp-imagemin';
import newer from 'gulp-newer';
import sync from 'browser-sync';
import fonter from 'gulp-fonter';
import getFolderSize from 'get-folder-size';
import replace from 'gulp-replace';
import gulpPug from 'gulp-pug';


// production build goes to docs folder

// if you use scss - styles from all files (except for 3rd party css) 
// must be imported into style.scss in src/styles/scss folder

// css styles must be also imported into style.scss

// if you DO NOT use scss - styles from all files must be imported into style.css in src/styles/css folder

// js from all files (except for 3rd party js) must be imported into src/main.js in script folder


const paths = {
    browserSync: {
        base: 'docs/'
    },
    del: {
        base: 'docs/*',
        exceptions: '!docs/images'
    },
    html: {
        src: 'src/*.html',
        dest: 'docs/'
    },
    pug: {
        src: 'src/*.pug',
        dest: 'docs/'
    },
    styles: {
        src: 'src/styles/scss/style.scss',
        dest: './docs/css/'
    },
    css: {
        src: 'src/styles/css/style.css',
        dest: 'docs/css/'
    },
    vendorStyles: {
        src: 'src/styles/vendorCSS/*.css',
        dest: 'docs/css/'
    },
    vendorJS: {
        src: 'src/script/vendorJS/*.js',
        dest: 'docs/js/'
    },
    scripts: {
        srcWatch: 'src/script/*.js',
        src: 'src/script/main.js',
        dest: 'docs/js/'
    },
    images: {
        src: 'src/images/**',
        dest: 'docs/images'
    },
    fonts: {
        src: 'src/fonts/*',
        dest: 'docs/fonts'
    },
};

const browserSync = sync.create();
const sass = gulpSass(dartSass);

export const clean = () => {
    return deleteAsync([paths.del.base, paths.del.exceptions]);
};

export const fonts = () => {
    return gulp.src(paths.fonts.src)
        .pipe(newer(paths.fonts.dest))
        .pipe(fonter({
            subset: [],
            formats: ['woff']
        }))
        .pipe(gulp.dest(paths.fonts.dest))
        .pipe(browserSync.stream());
};

export const HTML = () => {
    return gulp.src(paths.html.src)
        .pipe(replace('%NO_CACHE%', new Date().getTime()))
        .pipe(gulp.dest(paths.html.dest))
        .pipe(browserSync.stream());
};

export const pug = () => {
    return gulp.src(paths.pug.src)
        .pipe(gulpPug({ pretty: true }))
        .pipe(replace('%NO_CACHE%', new Date().getTime()))
        .pipe(gulp.dest(paths.pug.dest))
        .pipe(browserSync.stream());
};

export const vendorStyles = () => {
    return gulp.src(paths.vendorStyles.src)
        .pipe(gulp.dest(paths.styles.dest))
        .pipe(browserSync.stream());
};

export const vendorJS = () => {
    return gulp.src(paths.vendorJS.src)
        .pipe(gulp.dest(paths.vendorJS.dest))
        .pipe(browserSync.stream());
};

export const styles = () => {
    return gulp.src([paths.styles.src], { base: 'src/styles/scss' })
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(cleanCss({
            level: 2
        }))
        .pipe(rename({
            basename: 'main',
            suffix: '.min'
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.styles.dest))
        .pipe(browserSync.stream());
};

export const css = () => {
    return gulp.src([paths.css.src], { base: 'src/styles/css' })
        .pipe(sourcemaps.init())
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(cleanCss({
            level: 2
        }))
        .pipe(rename({
            basename: 'main',
            suffix: '.min'
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.css.dest))
        .pipe(browserSync.stream());
};

export const scripts = async () => {
    const bundle = await rollup.rollup({
        input: paths.scripts.src,
        plugins: [resolve(), babel({ babelHelpers: 'bundled' })]
    });

    await bundle.write({
        file: `${paths.scripts.dest}main.min.js`,
        format: 'iife',
        name: 'main',
        sourcemap: true,
        plugins: [terser()]
    });
    browserSync.reload();
};

export const img = () => {
    return gulp.src(paths.images.src)
        .pipe(newer(paths.images.dest))
        .pipe(imagemin())
        .pipe(gulp.dest(paths.images.dest))
        .pipe(browserSync.stream());
};

export const folderSize = async () => {
    const size = await getFolderSize.loose(paths.browserSync.base);
    console.log(`The 'docs' folder is ${(size / 1024 / 1024).toPrecision(3)} MB large`);
};

export const watch = () => {
    browserSync.init({
        server: {
            baseDir: paths.browserSync.base
        }
    });

    gulp.watch(paths.html.src, HTML);
    gulp.watch(paths.pug.src, pug);
    gulp.watch(paths.scripts.srcWatch, scripts);
    gulp.watch(paths.styles.src, styles);
    gulp.watch(paths.css.src, css);
    gulp.watch(paths.vendorStyles.src, vendorStyles);
    gulp.watch(paths.vendorJS.src, vendorJS);
    gulp.watch(paths.images.src, img);
    folderSize();
};

export const buildCSS = gulp.series(
    clean, HTML, gulp.parallel(scripts, css, vendorStyles, vendorJS, img, fonts), watch
);

export const build = gulp.series(
    clean, HTML, gulp.parallel(scripts, styles, vendorStyles, vendorJS, img, fonts), watch
);

export const buildPug = gulp.series(
    clean, pug, gulp.parallel(scripts, styles, vendorStyles, vendorJS, img, fonts), watch
);

export default build;