import $ from 'jquery';
class Apartments{
    constructor(data) {
        this.idCopmlex = data.idCopmlex;
        this.type = data.type;
        this.loader = data.loader;

        this._wrapperId = data.idCopmlex;
        this._wrapper = $('.js-s3d__wrapper__' + this._wrapperId);
        this.click = data.click;
        this.scrollToBlock = data.scrollToBlock;
    }

    init(config){
      $('.s3d-filter__plan').removeClass('s3d-filter__plan-active');
      this.getPlane(config);

      const self = this;
      $('.js-switch-btn').on('change', function() {
          let has = $(this).is(':checked');
          if(has && self.conf.plan3d) {
              self.conf.$img.src = self.conf.plan3dSrc;
              self.conf.$mfpLink.href = self.conf.plan3dSrc;
          } else {
              self.conf.$img.src = self.conf.planStandartSrc;
              self.conf.$mfpLink.href = self.conf.planStandartSrc;
          }
      });
    }
    update(config){
      $('.s3d-filter__plan').removeClass('s3d-filter__plan-active');
      this.getPlane(config);
    };

    updateImage(){
        const type = $('.js-flat-plan-mfp').data('type');
        return{
            $img : document.querySelector('.flat-plan'),
            $mfpLink : document.querySelector('.js-flat-plan-mfp'),
            planStandartSrc : $('.js-flat-plan-mfp').attr('href'),
            planStandartName : type ,
            plan3dSrc : `${window.location.origin}/wp-content/themes/boston/assets/img/projects/1/3d/${ type.split('_')[0]}.jpg`,
            plan3d : false
        }
    }

    checkImage() {
        let conf = this.conf;
        fetch(conf.plan3dSrc)
            .then(res => res.ok ? res : Promise.reject(res))
            .then(res => {
                $('.s3d-filter__plan').addClass('s3d-filter__plan-active');
                conf.plan3d = true;
            }).catch(()=> {
                $('.s3d-filter__plan').removeClass('s3d-filter__plan-active');
            })
    }
    /**Буква "Є" не воспринимается в адресной строке */
    changeYe() {
        $('.s3d-floor__helper-img img').src = $('.s3d-floor__helper-img img').src.replace(/%D0%84/, 'Ye');
        $('.s3d-floor__helper-img img').src = $('.s3d-floor__helper-img img').src.replace(/Є/, 'Ye');
        $('.flat-plan').src =$('.flat-plan').src.replace(/%D0%84/, 'Ye');
        $('.js-flat-plan-mfp').href = $('.js-flat-plan-mfp').href.replace(/Є/, 'Ye');
        $('.js-flat-plan-mfp').href = $('.js-flat-plan-mfp').href.replace(/%D0%84/, 'Ye');
    }



    getPlane(config){
        let attr = 'action=getFlatById&id='+config.flat;
        $.ajax({
            type: 'POST',
            // url: './static/apPars.php',
            url: '/wp-admin/admin-ajax.php',
            data: attr,
            success: response => this.setPlaneInPage(response)
        })
    }

    setPlaneInPage(response){
      $('#js-s3d__'+ this.idCopmlex).html(JSON.parse(response));
      this.loader.hide(this.type);
      $('.flat__floor').on('click', 'polygon', this.openPopup);
      // $('#js-s3d__wrapper__apart .form-js').on('click',()=> $('.common-form-popup-js').addClass('active'));
      // $('.js-flat-button-return').on('click', e => {
      //     e.preventDefault();
      //     $('.js-s3d-select__floor').click();
      // });
      $('.js-s3d-popup__mini-plan svg').on('click', 'polygon', (e)=>{
        this.activeSvg = $(e.target).closest("svg");
        $(this.activeSvg).css({'fill':''});
        $('.s3d-floor__helper').css({'opacity':0,'top':'-10000px'});
        this.click(e, 'floor');
        $('.js-s3d-popup__mini-plan').removeClass('active');
      });
  
      $('.flat__img').magnificPopup({
        type: 'image',
      });;
      this.conf = this.updateImage();
      this.checkImage();
    }

    openPopup() {
        $('.js-s3d-popup__mini-plan').addClass('active');
        $('.js-s3d-popup__mini-plan__close').on('click', () => $('.js-s3d-popup__mini-plan').removeClass('active') );
    }
}

export default Apartments;
