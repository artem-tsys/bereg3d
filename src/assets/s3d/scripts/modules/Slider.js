// import $ from 'jquery';
import Svg from './Svg';
import isDevice from './checkDevice';
import $ from "jquery";
// import Svg from './Svg';

class Slider {
  constructor(data) {
    this.type = data.type;
    this.browser = data.browser;
    this._x = 0;
    this._pret = 0;
    this._card = 0;
    this.amount = 0;
    this._urlBase = data.url;
    this._imageUrl = data.imageUrl;
    this._activeElem = data.activeSlide;
    this._wrapperId = data.idCopmlex;
    this._wrapper = $('.js-s3d__wrapper__' + this._wrapperId);
    this._ctx = document.getElementById('js-s3d__'+this._wrapperId).getContext('2d'); // Контекст
    this._wrapperEvent = '.js-s3d__svgWrap';
    this._height = 1080;
    this._width = 1920;
    this.flagMouse = false;
    this.currentSlide =  data.activeSlide;
    this.nextSlide =  data.activeSlide;
    this.openHouses = data.openHouses;

    this._eventsName = {
      start: 'mousedown',
      end: 'mouseup',
      leave: 'mouseleave',
      move: 'mousemove'
    };

    this.svgConfig = data;
    this.controllPoint = data.controllPoint;
    this.images = [];
    this.result = {
      min: data.numberSlide.min,
      max: data.numberSlide.max
    };
    this.mouseSpeed = data.mouseSpeed;
    this.numberSlide = {
      min: data.numberSlide.min,
      max: data.numberSlide.max
    };
    this.startDegCompass = 360 / this.numberSlide.max;
    this.infoBox = '';
    this.activeSvg = null;
    this.activeFloor = null;
    this.rotate = true;
    this.animates = () => {};

    this.updateInfo = this.updateInfo.bind(this);
    this.hiddenInfo = this.hiddenInfo.bind(this);
    this._ActiveHouse = data._ActiveHouse;
    this.resize = this.resize.bind(this);
    this.init = this.init.bind(this);
    this.click = data.click;
    this.setActiveSvg = this.setActiveSvg.bind(this);
    this.compass = data.compass;
    this.left = this.left.bind(this);
    this.right = this.right.bind(this);
    this.changeNext = this.changeNext.bind(this);
    this.changePrev = this.changePrev.bind(this);
    this.updateActiveFloor = this.updateActiveFloor.bind(this);
    this.loader = data.loader;
    this.progress = 0
    this.loadImage = this.loadImage.bind(this)
  }

  init(conf) {
    let self = this;

    if(isDevice('ios')) {
          this.mouseSpeed = 0.5;
      }
    if( isDevice() ){
      this._eventsName = {
            start: 'touchstart',
            end: 'touchend',
            leave: 'touchcancel',
            move: 'touchmove'
        }
    } else {
      this._wrapper.on( this._eventsName.end +' '+this._eventsName.leave , e => {
        if(e.target.classList.contains('s3d__button')) return;
        this.activeAnimate(false);
        this.amount = 0;
        if( this.flagMouse ){
          this.flagMouse = false;
          this.rewindToPoint.call(this);
        }
      });

      this._wrapper.on(this._eventsName.start, e => {
        if(e.target.classList.contains('s3d__button')) return;
        this.hiddenInfo(e);
        this.rotateStart.call(this,e);
        this.activeAnimate(true);
      });

      this._wrapper.on(this._eventsName.move, this._wrapperEvent, e => {
        if(this.flagMouse && this.rotate){
          this.activeSvg = $(e.target).closest("svg");
          $(this.activeSvg).css({'opacity':'0'});

          this.checkMouseMovement.call(this, e);
        } else if(e.target.tagName === 'polygon') {
          this.updateInfo(e);
        } else {
          this.hiddenInfo(e);
        }
      });
    }

    this._wrapper.on('click', 'polygon', e =>{
        this._ActiveHouse.set(+e.target.dataset.build);
        if(!this.openHouses.includes(+e.target.dataset.build)){
            this.updateInfo(e);
            return;
        }
        this.activeFloor = +e.target.dataset.floor;
        $('.js-s3d__svgWrap .active-floor').removeClass('active-floor');
        $(e.target).addClass('active-floor');
        this.activeSvg = $(e.target).closest("svg");
        $(this.activeSvg).css({'opacity':''});
        this.compass.save(this.compass.current);
        this.click(e, this.type);
    });

    this.firstLoadImage();
    this.createSvg();
    this.createInfo();
    this.createArrow();
  }

  setConfig(data){
    this.type          = data.type || this.type;
    this._urlBase      = data.url || this._urlBase;
    this._imageUrl     = data.imageUrl || this._imageUrl;
    this._activeElem   = data.activeSlide || this._activeElem;
    this._wrapperId    = data.idCopmlex || this._wrapperId;
    this.currentSlide  = data.activeSlide || this.currentSlide;
    this.nextSlide     = data.activeSlide || this.nextSlide;
    this.svgConfig     = data || this.svgConfig;
    this.controllPoint = data.controllPoint || this.controllPoint;
    this.mouseSpeed    = data.mouseSpeed || this.mouseSpeed;
  }
  update(config) {
    this.loader.hide(this.type)
  }
  updateImage(){
    // let self = this;
    this.progress = 0
    this._ctx.canvas.width = this._width;
    this._ctx.canvas.height = this._height;
    this.loadImage(0)

    this.setActiveSvg(this._ActiveHouse.get());
  }

  firstLoadImage() {
    $('.js-s3d__slideModule').addClass('s3d-unActive')
    this._ctx.canvas.width = this._width
    this._ctx.canvas.height = this._height
    const self = this
    const img = new Image()
    const index = this._activeElem
    img.src = `${this._imageUrl + index}.jpg`
    img.dataset.id = index
    img.onload = function load() {
      self.images[index] = this
      const deg = (self.startDegCompass * self._activeElem) + (self.startDegCompass * 57)
      $('.s3d-controller__compass svg').css('transform', `rotate(${deg}deg)`)
      self.compass.save(index)
      self._ctx.drawImage(this, 0, 0, self.width, self.height)
      self.rotate = false
      self.resizeCanvas()
      self.loadImage(0)
    }
  }

  loadImage(i, type) {
    const self = this
    const img = new Image()
    const index = i
    img.src = `${this._imageUrl + index}.jpg`
    img.dataset.id = index
    img.onload = function load() {
      self.images[index] = this
      self.progressBarUpdate()
      if (index === self.numberSlide.max) {
        self.resizeCanvas()
        self._ctx.drawImage(self.images[self._activeElem], 0, 0, self._width, self._height)
        self.loader.hide(self.type)
        self.rotate = true
        return index
      }
      return self.loadImage(i + 1)
    }

    img.onerror = function (e) {
      if (type === 'error') {
        self.sendResponsiveError(this, self)
      } else {
        self.loadImage(+this.dataset.id, 'error')
      }
    }
  }

  sendResponsiveError(elem, self, type) {
    const res = Object.assign(self.browser, {
      project: 'bereg --prod',
      url: elem.src || elem.dataset.id || 'пусто',
      type: type || 'error',
      text: 'new'
    })
    $.ajax('/wp-admin/admin-ajax.php', {
      method: 'POST',
      data: {
        data: res,
        action: '3dDebuger',
      },
    }).then(resolve => console.log(resolve))
  }

  progressBarUpdate() {
    if (this.progress >= this.numberSlide.max) {
      setTimeout(() => {
        $('.fs-preloader').removeClass('preloader-active')
      }, 300)
      return
    }
    this.progress += 1
    const prcent = this.progress * (100 / (this.numberSlide.max + 1))
    $('.fs-preloader-amount').html(Math.ceil(prcent))
  }

  resizeCanvas() {
      let factorW = this._width / this._height;
      let factorH = this._height / this._width;
      let canvasWrapp = $('.js-s3d__wrapper__complex');
      let canvas = $('#js-s3d__complex');
      let diffW = this._width / canvasWrapp.width();
      let diffH = this._height / canvasWrapp.height();

      if(diffW < diffH ) {
        canvas.width( canvasWrapp.width() );
        canvas.height( canvasWrapp.width() * factorH );
      } else {
        canvas.height( canvasWrapp.height() );
        canvas.width( canvasWrapp.height() * factorW);
      }
      this.centerSlider(this._wrapper[0])
  }

  centerSlider(elem) {
    const scroll = (elem.scrollWidth - document.documentElement.offsetWidth) / 2
    this._wrapper.scrollLeft(scroll)
  }

  setActiveSvg(house){
    $('.js-s3d__container-active').removeClass('js-s3d__container-active');
    $('.js-s3d__svg-container'+ house).addClass('js-s3d__container-active');
  }

  changeNext() {
    if(this._activeElem === this.numberSlide.max) {
      this.result.max = this.controllPoint[0];
      this.result.min = -1;
      this._activeElem = this.numberSlide.min;
    } else {
      this._activeElem++;
    }
    this.compass.set(this._activeElem);
    this._ctx.drawImage(this.images[this._activeElem], 0, 0,this._width, this._height );
  }

  changePrev() {
    if(this._activeElem === this.numberSlide.min) {
      this.result.max = this.numberSlide.max + 1;
      this.result.min = this.controllPoint[this.controllPoint.length - 1];
      this._activeElem = this.numberSlide.max;
    } else {
      this._activeElem--;
    }

     this.compass.set(this._activeElem);
    this._ctx.drawImage(this.images[this._activeElem], 0, 0,this._width, this._height );
  }

  checkMouseMovement(e) {
      // get amount slide from a touch event
        this._x = e.pageX || e.targetTouches[0].pageX;
        this.amount += +( (this._x - this._pret) / ( window.innerWidth / this.numberSlide.max / this.mouseSpeed )).toFixed(0);
    }

  rewindToPoint(){
    this.cancelAnimateSlide();
    if(!this.controllPoint.includes(this._activeElem)) {
      this.controllPoint.map( el => {
        if (el < this._activeElem && el > this.result.min){
          this.result.min = el;
        } else if (el > this._activeElem && el < this.result.max){
          this.result.max = el;
        }
      });

      if(this.result.min === 0){
        this.result.min = this.controllPoint[this.controllPoint.length - 1] - this.numberSlide.max;
      }

      if(this.result.max === this.numberSlide.max){
        this.result.max = this.controllPoint[0] + this.numberSlide.max;
      }

      this.checkResult();
    } else {
        this.updateSvgActive(this.type,'_activeElem');
    }
  }

  updateSvgActive(wrap,current){
      $(this.activeSvg).css({'opacity':''});
      let clas = this.type === 'house'?'.js-s3d__svg-container'+ this._ActiveHouse.get() + ' ': '.js-s3d__svg-container__complex ';
      $( clas + '.js-s3d__svg__active').removeClass('js-s3d__svg__active');
      $( clas + '.'+ wrap + '__' + this[current]).addClass('js-s3d__svg__active');
      this.currentSlide = this[current];
  }

  repeatChangeSlide(fn) {
     return setInterval( () => {
         fn();
         if( this._activeElem === this.nextSlide) {
             this.cancelAnimateSlide();
             this.updateSvgActive(this.type,'nextSlide');
             $(this.activeSvg).css({'opacity':''});
         }
     },30);
  }

  checkResult(){
    if( (this.result.max - this.result.min) / 2 + this.result.min <= this._activeElem ){
      if(this.result.max <= this.numberSlide.max) {
          this.nextSlide = this.result.max;
      } else {
          this.nextSlide = this.controllPoint[0];
      }
        this.repeat = this.repeatChangeSlide(this.changeNext.bind(this));

    } else {
        if(this.result.min > this.numberSlide.min) {
            this.nextSlide = this.result.min;
        } else {
            this.nextSlide = this.controllPoint[this.controllPoint.length - 1];
        }
        this.repeat = this.repeatChangeSlide(this.changePrev.bind(this));
    }
  }

  cancelAnimateSlide() {
    clearInterval(this.repeat);
    this.repeat = undefined;
    this.result.min = this.numberSlide.min;
    this.result.max = this.numberSlide.max;
  }

  createInfo(){
    let infoBox = createMarkup('div','.js-s3d__slideModule', {class:'js-s3d__infoBox'} );
    let infoBoxContent = `<ul>
<!--        <li class="js-s3d__infoBox__house">${textContent.helperFloor.house}: <span>5</span></li>-->
        <!--<li class="js-s3d__infoBox__section">section: <span>7</span>-->
        <!--<li class="js-s3d__infoBox__apartments">apartments: <span>10</span></li>-->
        <li class="js-s3d__infoBox__floor"><span>10</span> ${textContent.helperFloor.floor}</li>
    </ul>`;
    $(infoBox).append(infoBoxContent);
    this.infoBox = $(infoBox);
  }

  updateInfo(e){
      const pos = $('.js-s3d__wrap').offset();
    // положение курсора внутри элемента
      const Xinner = e.pageX - pos.left;
      const Yinner = e.pageY - pos.top;
      this.infoBox.css({'opacity' : '1'});
      this.infoBox.css({'top' : Yinner - 40});
      this.infoBox.css({'left' : Xinner});
      if(this.openHouses.includes(+e.target.dataset.build)){
          // this.infoBox.find('.js-s3d__infoBox__house')[0].innerHTML = `${textContent.helperFloor.house}:  ${e.target.dataset.build || ''}` ;
          this.infoBox.find('.js-s3d__infoBox__floor')[0].innerHTML = `${textContent.helperFloor.floor}:  ${e.target.dataset.floor || ''}` ;
          this.infoBox.find('.js-s3d__infoBox__floor')[0].style.display = '';
      } else {
          // this.infoBox.find('.js-s3d__infoBox__house')[0].innerHTML = textContent.helperFloor.noSale;
          this.infoBox.find('.js-s3d__infoBox__floor')[0].style.display = 'none';

      }
  }

  updateInfoFloorList(e){
      const data =  (e.target || e).dataset;
      let list = $('.js-s3d__svgWrap .floor-svg-polygon[data-build='+ data.build +'][ data-floor='+ data.floor +']');

      list.each((i,el) =>{
          this.updateInfoFloor(el, data);
      });

      if(this.openHouses.includes(+data.build)){
          $('[data-build='+ data.build +'] .floor-text').html(data.floor);
      } else {
          $('[data-build='+ data.build +'] .floor-text').html('будинок не у продажу');
      }
   }

  updateInfoFloor(e, data){
        // положение курсора внутри элемента
      const parent = $(e).closest('svg');
      const widthSvgPhoto = parent.attr('viewBox').split(' ')[2];
      const bbox = e.getBBox();
      const height = (widthSvgPhoto / 13) * 0.2;
      const y =  (bbox.y + (bbox.height / 2));
      $(parent).find('.floor-info-svg[data-build='+ data.build +']').addClass("active-floor-info").attr('y', y - (height/2));
    }

  updateActiveFloor(floor){
      this.activeFloor = floor;
      let nextFloorSvg = $('.js-s3d__svg__active [data-build='+this._ActiveHouse.get()+'][data-floor='+this.activeFloor+']')[0];
      this.updateInfoFloorList(nextFloorSvg);
      $('.js-s3d__svgWrap .active-floor').removeClass('active-floor');
      $('.js-s3d__svgWrap [data-build='+this._ActiveHouse.get()+'][data-floor='+this.activeFloor+']').addClass('active-floor');
  }

  hiddenInfo(){
      this.infoBox.css({'opacity' : '0'});
      this.infoBox.css({'top' : '-10000px'});
      this.infoBox.css({'left' : '-10000px'});
  }
  hiddenInfoFloor(){
      // $('.active-floor-info').removeClass("active-floor-info");
  }
  showInfoFloor(){
      // $('.active-floor-info').addClass("active-floor-info");
      // this.infoBoxFloor.css({'opacity' : '1', 'z-index': ''});
  }

  rotateStart(e) {
        this.cancelAnimateSlide();
        this._x = e.pageX || e.targetTouches[0].pageX;
        this._pret = e.pageX || e.targetTouches[0].pageX;
        this.flagMouse = true;
        this.activeSvg = $(e.target).closest("svg");
        $(this.activeSvg).css({'opacity':'0'});
    }

  resize() {
    this._height = this._wrapper.height();
    this._width = this._wrapper.width();
    this._ctx.canvas.width = this._width;
    this._ctx.canvas.height = this._height;
    this._ctx.drawImage(this.images[this._activeElem], 0, 0, this._width, this._height);
  }

  createSvg(){
    let svg = new Svg(this.svgConfig);
    svg.init(this.setActiveSvg.bind(this, this._ActiveHouse.get()));

    // подсветить svg неактивного дома
  }

  createArrow() {
      let arrowLeft = createMarkup('button', this._wrapper, {class: 's3d__button s3d__button-left js-s3d__button-left unselectable'});
      $(arrowLeft).append('<svg width="50" height="9" viewBox="0 0 50 9" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
        '<path d="M7 -3.75918e-06L3.93402e-07 4.5L7 9L7 -3.75918e-06Z" fill="#FCFCFC"/>\n' +
        '<path d="M50 4L50 5L7 4.99999L7 4L50 4Z" fill="#FCFCFC"/>\n' +
        '</svg>');
      $('.js-s3d__button-left').on('click', this.left);

      let arrowRight = createMarkup('button', this._wrapper, {class: 's3d__button s3d__button-right js-s3d__button-right unselectable'});
      $(arrowRight).append('<svg width="50" height="9" viewBox="0 0 50 9" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
        '<path d="M43 9L50 4.5L43 0V9Z" fill="#FCFCFC"/>\n' +
        '<path d="M5.96046e-08 5L0 4L43 4V5L5.96046e-08 5Z" fill="#FCFCFC"/>\n' +
        '</svg>');
      $('.js-s3d__button-right').on('click', this.right);
  }

  right(){
      if(this.rotate){
        this.rotate = false;
        let amount = 0;
        this.controllPoint.forEach( (el, i) => {
            if(this._activeElem === el ) {
                if(i === 0){
                    amount = (this.numberSlide.max - this.controllPoint[this.controllPoint.length - 1]) + ( this.controllPoint[0] - this.numberSlide.min );
                } else {
                    amount = this.controllPoint[i] - this.controllPoint[i - 1] - 1;
                }
            }
        });
        this.changeSlide(amount, this.changePrev);
      }
  }

  left(){
      if(this.rotate) {
        this.rotate = false;
        let amount = 0;
        this.controllPoint.forEach( (el, i) => {
            if(this._activeElem === el ) {
                if(i === this.controllPoint.length-1){

                    amount = (this.numberSlide.max - this.controllPoint[i]) + ( this.controllPoint[0] - this.numberSlide.min ) ;
                } else {
                    amount = this.controllPoint[i + 1] - this.controllPoint[i] - 1;
                }
            }
        });
        this.changeSlide(amount, this.changeNext);
      }
  }

  changeSlide(amount, fn){
      let index = 0;
      $('.js-s3d__svg-container').css({'opacity':0});
      let timeout = setInterval(() => {
          fn();
          if(index >= amount) {
              clearInterval(timeout);
              this.updateSvgActive(this.type,'_activeElem');
              $(this.activeSvg).css({'opacity':''});
              $('.js-s3d__svg-container').css({'opacity':1});
              this.rotate = true;
              return;
          }

          index++;
      },30);
  }

  activeAnimate(flag) {
      if (flag) {
          this.animates = this.animate();
      } else {
          window.cancelAnimationFrame(this.animates);
      }
  }
  animate() {
        if( this.amount >= 1 ){
            this.changeNext();
            this.amount-=1;
            this._pret = this._x;
        } else if( this.amount <= -1 ){

            this.changePrev();
            this.amount+=1;
             this._pret = this._x;
         }
         this.animates = requestAnimationFrame(this.animate.bind(this));
        }
  }

export default Slider;
