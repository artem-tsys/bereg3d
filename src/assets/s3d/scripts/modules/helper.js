import isDevice from "./checkDevice";
import $ from "jquery";

export default class Helper{
	constructor(data) {
		this.text = data
		this.init()
	}

	init(){

		this.createHtml('.js-s3d__slideModule', isDevice('mobile') ? this.text.mobile : this.text.desktop, this.text.general)

		if(!window.localStorage.getItem('helps')) {
			$('.js-first-info').css({'visibility' :'visible'});
		}

		$('.js-first-info__button').on('click', e => {
			switch (e.target.dataset.type){
				case 'next':
					let step = $('.js-first-info-step.active').removeClass('active').data('step');
					$(`.js-first-info-step[data-step="${step+1}"]`).addClass('active');
					break;
				case 'end':
					$('.js-first-info-step.active').removeClass('active');
					window.localStorage.setItem('helps',true);
					$('.js-first-info').css({'visibility' : ''});
					break;
			}
		})
	}

	createHtml(wrap, data, general){
		general['length'] = data.length
		const html = `
		<div class="wrapper__first-info js-first-info">
			${data.reduce( (result, el, i) => result + this.createStepHtml(el, i+1, general),'')}
		</div>
		`
		$(wrap).append(html)
	}

	createStepHtml(el, step, general){
		let steps = ``
		for(let i = 1; i <= general.length; i++){
			steps += `<span class="first-info__stage-${i} ${ i === step ? "active":""}" ></span>`
		}
		return `
			 <div class="first-info__content first-info js-first-info-step ${step === 1 ? "active": ""}" data-step=${step}>
        <div class="first-info__header">
            <div class="first-info__header__logo-wrap">
                <img src=${general['head-logo']} alt="mini-logo" class="first-info__header__logo-img">
            </div>
            <div class="first-info__title">${general['head-description']}</div>
            <div class="first-info__step">${el.title}</div>
        </div>
            <img src=${el.img} alt="step${step}" class="first-info__image">
            <img src=${general.logo} alt="logo" class="first-info__logo">
            <p class="first-info__text">${el.text}</p>
            <button type="button" class="button button_text js-first-info__button" data-type=${ general.length === step ? "end":"next"}> ${general['button-next']} </button>
            <button type="button" class="button button_text js-first-info__button" data-type="end"> ${general['button-end']} </button>
            <div class="first-info__stage">
            		${steps}
<!--                <span class="first-info__stage-1"></span>-->
<!--                <span class="first-info__stage-2"></span>-->
<!--                <span class="first-info__stage-3 active"></span>-->
            </div>
    </div>
		`
	}
}
