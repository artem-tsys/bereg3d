import $ from 'jquery';
import App from './modules/App';
import isDevice from './modules/checkDevice';
import isBrowser from './modules/checkBrowser';
import CreateMarkup from './modules/markup';
import loader from './modules/loaderTime';

let lang = 'ru';
window.nameProject = 'bereg';
window.defaultProjectPath = `/wp-content/themes/${window.nameProject}/assets`;
window.defaultModulePath = `/wp-content/themes/${window.nameProject}/assets/s3d/`;
window.defaultStaticPath = `/wp-content/themes/${window.nameProject}/static/`;
// window.status = 'local';
window.status = 'dev';
// window.status = 'prod';

document.addEventListener('DOMContentLoaded',function (global) {
    $.ajax({
        url: `/wp-content/themes/${nameProject}/assets/s3d/textHelper.json`,
        success: response => {
            $('.lang-block__item').each((i, el) => {
               if(!$(el).hasClass('inactive')){
                   let n = $(el).find('a').html().toLowerCase();
                   if(n === 'рус' || n === 'ru'){
                       lang = 'ru';
                   } else if(n === 'eng' || n === 'en') {
                       lang = 'en';
                   } else {
                       lang = 'ua';
                   }
               }
            });
            window.textContent = response[lang];
            init();
        }
    });

});

function init() {
    window.createMarkup = CreateMarkup;
    const config = {
        complex: {
            url: '',
            imageUrl: `/wp-content/themes/${nameProject}/assets/s3d/images/${nameProject}/`,
            id: 'js-s3d__wrapper',
            numberSlide: {
                min: 0,
                max: 178
            },
            controllPoint : [25,55,110,165],
            activeSlide: 25,
            mouseSpeed: 1,
            // mouseSpeed: 300,
        },
        floor: {
            id: 'js-s3d__wrapper'
        },
        apart: {
            id: 'js-s3d__wrapper'
        },
        openHouses : [1]
    };


    let app;
    new Promise(resolve => {
        loader(resolve)
    }).then(value => {
        document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
        // if (!value.fastSpeed) {
        //     config.complex.imageUrl += 'mobile/'
        // }
        
        if (isDevice('mobile')) {
            $('.js-s3d__slideModule').addClass('s3d-mobile')
        }
        config.complex['browser'] = Object.assign(isBrowser(), value);
        app = new App(config);
        app.init();

        $(window).resize(() => {
        	app.resize();
          document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`)
        })
    })
}
