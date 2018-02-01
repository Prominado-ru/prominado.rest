let gulp = require('gulp'),
    iconv = require('gulp-iconv'),
    del = require('del'),
    git = require('gulp-git'),
    path = require('path'),
    tar = require('gulp-tar'),
    gzip = require('gulp-gzip'),
    file = require('gulp-file'),
    os = require('os'),
    moment = require('moment'),
    sequence = require('run-sequence');

const buildFolder = 'build';
const distrFolder = 'dist';

// Очистка директории со сборкой
gulp.task('clean', function () {
    del(buildFolder);
});

// Копирование всех файлов модуля в директорию сборки
gulp.task('move', function () {
    return gulp.src(
        [
            './**',
            '!./{node_modules,node_modules,dist,build/**}',
            '!./*.js',
            '!./*.json',
            '!./*.md'
        ]
    ).pipe(gulp.dest(buildFolder));
});

// Кодирование в 1251
gulp.task('encode', function () {
    return gulp.src([
        path.join(buildFolder, '**/*.php'),
        path.join(buildFolder, '**/*.js')
    ], {dot: true})
        .pipe(iconv({encoding: 'win1251'}))
        .pipe(gulp.dest(buildFolder));
});

// Архивирует в tar.gz
gulp.task('archive', function () {
    return gulp.src(path.join(buildFolder, '**/*'))
        .pipe(tar('.last_version.tar'))
        .pipe(gzip())
        .pipe(gulp.dest(buildFolder));
});

// Переносит в директорию с дистрибутивом
gulp.task('dist', function () {
    return gulp.src(path.join(buildFolder, '.last_version.tar.gz'))
        .pipe(gulp.dest(distrFolder));
});

// Сборка текущей версии модуля
gulp.task('build_last_version', function (callback) {
    sequence('clean', 'move', 'encode', 'archive', 'dist', 'clean', callback);
});

// Сборка всего модуля
gulp.task('build', function (callback) {
    sequence('build_last_version', callback);
});