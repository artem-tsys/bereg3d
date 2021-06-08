// import $ from 'jquery';
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
    initPlan(conf) {
      fetch(conf['3d'])
        .then(res => (res.ok ? res : Promise.reject(res)))
        .then(() => {
          $('.plan').addClass('plan-active');
        }).catch(() => {
        $('.plan').removeClass('plan-active');
      });
    }

    init(config){
      const setSrcImage = (path) => {
        this.image.setAttribute('src', path);
        this.image.setAttribute('data-mfp-src', path);
      };
      const updatePlanActiveText = (type) => {
        const prevActive = document.querySelector('.active[data-plane]');
        prevActive.classList.remove('active');
        const nextActive = document.querySelector(`[data-plane][data-type="${type}"]`);
        nextActive.classList.add('active');
      };

      $(document).on('change', '.js-switch-btn', (event) => {
        const type = (event.target.checked) ? '3d' : '2d';
        setSrcImage(this.pathImages[type]);
        updatePlanActiveText(type);
      });

      $('.s3d-filter__plan').removeClass('s3d-filter__plan-active');
      this.getPlane(config);

      // const self = this;
      // $('.js-switch-btn').on('change', function() {

          // let has = $(this).is(':checked');
          // if(has && self.conf.plan3d) {
          //     self.conf.$img.src = self.conf.plan3dSrc;
          //     self.conf.$mfpLink.href = self.conf.plan3dSrc;
          // } else {
          //     self.conf.$img.src = self.conf.planStandartSrc;
          //     self.conf.$mfpLink.href = self.conf.planStandartSrc;
          // }
      // });
    }
    update(config){
      $('.s3d-filter__plan').removeClass('s3d-filter__plan-active');
      this.getPlane(config);
    };

    // updateImage(){
    //     const type = $('.js-flat-plan-mfp').data('type');
    //     return{
    //         $img : document.querySelector('.flat-plan'),
    //         $mfpLink : document.querySelector('.js-flat-plan-mfp'),
    //         planStandartSrc : $('.js-flat-plan-mfp').attr('href'),
    //         planStandartName : type ,
    //         plan3dSrc : `${window.location.origin}/wp-content/themes/bereg/assets/img/projects/1/3d/${ type.split('_')[0]}.jpg`,
    //         plan3d : false
    //     }
    // }

    // checkImage() {
    //     let conf = this.conf;
    //     fetch(conf.plan3dSrc)
    //         .then(res => res.ok ? res : Promise.reject(res))
    //         .then(res => {
    //             $('.s3d-filter__plan').addClass('s3d-filter__plan-active');
    //             conf.plan3d = true;
    //         }).catch(()=> {
    //             $('.s3d-filter__plan').removeClass('s3d-filter__plan-active');
    //         })
    // }
    /**Буква "Є" не воспринимается в адресной строке */

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
      this.dataContainers = {
        // type: document.querySelector('[data-type="type"]'),
        flat: document.querySelector('[data-type="flat"]'),
        area: document.querySelector('[data-type="area"]'),
      };
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
      // this.conf = this.updateImage();
      // this.checkImage();

      this.image = document.querySelector('[data-flat-image]');
      this.pathImages = JSON.parse(this.image.dataset.src);
      this.initPlan(this.pathImages);
      
      const svgContainer = document.querySelector('.flat__floor');
      svgContainer.addEventListener('mouseleave', (event) => {
        this.hoverDataHundler(event.target, this.dataContainers);
      });
      svgContainer.addEventListener('mouseover', (event) => {
        this.hoverDataHundler(event.target, this.dataContainers);
      });
      $('.flat__img').magnificPopup({
        type: 'image',
      });
    }

    openPopup() {
      $('.js-s3d-popup__mini-plan').addClass('active');
      $('.js-s3d-popup__mini-plan__close').on('click', () => $('.js-s3d-popup__mini-plan').removeClass('active') );
    }
    
    updateHoverFlat(containers, data) {
      const wrap = containers;
      // wrap.type.innerHTML = data.flat_id;
      wrap.flat.innerHTML = data.rooms;
      wrap.area.innerHTML = data.area;
    }
    
    hoverDataHundler(hoverElement, dataContainers) {
      if (hoverElement.tagName === 'polygon') {
        const data = hoverElement.dataset;
        this.updateHoverFlat(dataContainers, data);
      } else {
        Object.values(dataContainers).forEach((element) => {
          const el = element;
          el.innerHTML = element.dataset.defaultValue;
        });
      }
    }
}

export default Apartments;
