import $ from 'jquery';
class Svg {
    constructor(data){
        this.complexData = {};
        this.imageUrl = data.imageUrl;
        this.id = data.id;
        this.numberSlide = data.numberSlide;
        this.controllPoint = data.controllPoint;
        this.activeSlide = data.activeSlide;
        this.mouseSpeed = data.mouseSpeed;
        this.idCopmlex = data.idCopmlex;
        this.type = data.type;
        this.click = data.click;

        this.idCopmlex = data.idCopmlex;
    }
    init(fn){
        this.setActiveSvg = fn;
        this.getData(this.selectSvg)
    }

    getData(fn) {
        let self = this;
      $.ajax('/wp-content/themes/bereg/assets/s3d/svg.json').done(function (msg) {
      // $.ajax('/wp-admin/svg.json').done(function (msg) {
          fn.call(self, msg)
      })
    }

    selectSvg(data) {
      if( this.type === 'complex') {
        this.createSvg(data.complex, this.type)
      } else if(this.type === 'house') {
        for(let i in data.house){ this.createSvg(data.house[i],i) }
        this.setActiveSvg();
      }
    }


    createSvg(data,name) {
        let svgContainer = createMarkup('div', '#js-s3d__wrapper__' + this.idCopmlex, {class:'js-s3d__svg-container js-s3d__svg-container' + (name === 'complex'? '__complex': name ) });
        for(let key in data) {
            let svgWrap = document.createElement('div');
            if(+key === +this.activeSlide){
              svgWrap.classList = 'js-s3d__svgWrap ' + this.type + '__' + key + ' js-s3d__svg__active';
            } else {
              svgWrap.classList = 'js-s3d__svgWrap ' + this.type + '__' + key;
            }
            $(svgContainer).append(svgWrap);
            $.ajax(data[+key].path).done(function (svg) {
              $(svgWrap).append(svg.documentElement );
            });

        }

    }
}


export default Svg;



