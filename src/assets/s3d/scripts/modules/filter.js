import ionRangeSlider from 'ion-rangeslider';
// import $ from 'jquery';


class Filter {
    constructor(config, data){
       this.wrapper = config.wrap || '';
       this.filterName = { range : ['area','floor','living','price','priceM2'], checkbox: ['house','rooms']};
       this.filter = {};
       this.nameFilterFlat = {
           area: 'all_room',
           living: 'life_room',
           house: 'build_name',
           floor: 'floor',
           rooms: 'rooms',
           price: 'price',
           priceM2: 'price_m2',
       }; // name key js and name key in flat
       this.filterSelect = {};
       this.flatList = data;
       this.currentAmountFlat = data.length;
       this.openHouses = config.openHouses;

        this.hidden = this.hidden.bind(this)
        this.show = this.show.bind(this)
    }

    init(config) {
      $('.js-s3d-filter__button--reset').on('click', () => this.resetFilter() );
      $('.js-s3d-filter__select').on('click','input', () => this.showSvgSelect());
      $('.js-s3d-filter__button--apply').on('click', () => $('.js-s3d-filter').removeClass('active'));
      $('.js-s3d-filter__show').on('click', () => this.showAvailableFlat());

      $('.js-s3d-filter__close').on('click', () => {
        $('.js-s3d-filter').removeClass('active');
      });
      $('.js-s3d-filter__open').on('click', () => {
        $('.js-s3d-filter').addClass('active');

        if( !$('.js-s3d-filter__show--input').prop('checked') ){
          $('.js-s3d-filter__show--input').click();
        }
      });

        this.filterName.checkbox.forEach(name => {
            let amount = $('.js-s3d-filter [data-type="house"]').length;
            for(let i = 0; i < amount; i++) {
              $('.js-s3d-filter #house-'+(i+1))[0].dataset[name] = i+1;
            };
        });
        this.filterName.range.forEach( name => {
            const classes = this.getAttrInput(name);

            if(classes) {
              for( let key in config[this.nameFilterFlat[name]] ){
                classes[key] = (key === 'min') ? Math.floor(+config[this.nameFilterFlat[name]][key]) : Math.ceil(+config[this.nameFilterFlat[name]][key])
              }
              this.createRange(classes);
            }
        });

        this.setAmountSelectFlat(this.flatList.length);
    }

    getNameFilterFlat(){return this.nameFilterFlat};
    showSvgSelect() {
        // фильтр svg , ищет по дата атрибуту, нужно подстраивать атрибут и класс обертки
        const data = this.applyFilter(this.flatList);
        this.setAmountSelectFlat(this.currentAmountFlat);
        for( let key in data) {
              if(+data[key].length > 0) {
                  // $('#js-s3d__wrapper__complex polygon[data-build="'+key+'"]').css({'opacity':0.5});
                  data[key].forEach(
                      floor => {
                          if ($('.js-s3d__svg-container__complex').length > 0)
                          // if ($('.js-s3d__svg-container' + key).length > 0)
                              $('#js-s3d__wrapper__complex polygon[data-build="'+key+'"][ data-floor="' + floor + '"]').css({'opacity': 0.5})
                      }
                  )
              }
          }
    }
    showAvailableFlat() {
        $('.js-s3d-filter__show--input').click();
        if($('.js-s3d-filter__show--input').prop('checked') ){
            this.showSvgSelect(this.flatList);
            $('.floor-info-helper').css('opacity', '1');
        } else {
            $('#js-s3d__wrapper polygon').css({'opacity': ''});
            $('.floor-info-helper').css('opacity', '0');
        };
    }

    // показать фильтр
    show() {
        $('.js-s3d-filter').addClass('active')
    }

    // спрятать фильтр
    hidden() {
        $('.js-s3d-filter').removeClass('active')
    }

    getAttrInput(name){
        return $('.js-s3d-filter__'+ name +'--input').length > 0 ? $('.js-s3d-filter__'+ name +'--input').data(): false;
    }
    getAttrSelect(name){
        let input = $('.js-s3d-filter__'+ name +'--input:checked').length ? $('.js-s3d-filter__'+ name +'--input:checked') : $('.js-s3d-filter__'+ name +'--input');

        let arr = {type :input.data('type'), value:[]};
        input.each((i,el) => arr.value.push($(el).data(name)));
        return arr;
    }

    createRange(config) {
        if(config.type !== undefined) {
            const self = this;
            let instance;
            let min = config.min;
            let max = config.max;
            let $min = $('.js-s3d-filter__' + config.type + '__min--input');
            let $max = $('.js-s3d-filter__' + config.type + '__max--input');
            $('.js-s3d-filter__' + config.type + '--input').ionRangeSlider({
                type: "double",
                grid: false,
                min: config.min || 0,
                max: config.max || 0,
                from: config.min || 0,
                to: config.max || 0,
                step: config.step || 1,
                onStart: updateInputs,
                onChange: updateInputs,
                onFinish: function(e){
                    updateInputs(e);
                    self.showSvgSelect();
                },
                onUpdate: updateInputs
            });
            instance = $('.js-s3d-filter__' + config.type + '--input').data("ionRangeSlider");

            function updateInputs (data) {
                $min.prop("value", data.from);
                $max.prop("value", data.to);
            }

            $min.on("change", function() {changeInput.call(this,'from')});
            $max.on("change", function() {changeInput.call(this,'to')});

            function changeInput(key) {
                let val = $(this).prop("value");
                if(key === 'from'){
                  if (val < min) val = min;
                  else if (val > instance.result.to) val = instance.result.to;
                } else if(key === 'to'){
                  if (val < instance.result.from) val = instance.result.from;
                  else if (val > max) val = max;
                }

                instance.update(key === 'from' ? {from: val} : {to: val});
                $(this).prop("value", val);
                self.showSvgSelect();
            }
        }
    }
    setRange(config){
        if(config.type !== undefined) {
            this.filter[config.type] = {};
            this.filter[config.type].type = 'range';
            this.filter[config.type].elem = $('.js-s3d-filter__' + config.type + '--input').data("ionRangeSlider");
        }
    }
    setCheckbox(config) {
        if(config.type !== undefined) {
            if(!this.filter[config.type] || !this.filter[config.type].elem ){
                this.filter[config.type] = {};
                this.filter[config.type].elem = [];
                this.filter[config.type].value = [];
                this.filter[config.type].type = 'select';
            }
            this.filter[config.type].elem = $('.js-s3d-filter__'+config.type + ' [data-type = '+config.type+']');
        }
    }

    resetFilter() {
        $('#js-s3d__wrapper polygon').css({'opacity': ''});

        for(let key in this.filter){
            if(this.filter[key].type === 'range') {
                this.filter[key].elem.update({from:this.filter[key].elem.result.min,to:this.filter[key].elem.result.max});
            } else {
                this.filter[key].elem.each( (i,el) => {el.checked? el.checked = false:''})
            }
        }
        this.showSvgSelect();
    }

    applyFilter(data) {
        this.clearFilterParam();
        this.checkFilter();
        this.getFilterParam();
        return this.filterFlat(data);
    }

    checkFilter() {
        this.filterName.range.forEach( name => {
            const classes = this.getAttrInput(name);
            if(classes) this.setRange(classes);
        });
        this.filterName.checkbox.forEach(name => this.setCheckbox(this.getAttrSelect(name)));
    }

    setAmountSelectFlat(amount) {
        $('.js-s3d-filter__open-num').html(amount);
        $('.js-s3d-filter__amount-flat__num').html(amount);
    }
    filterFlat(data) {
        this.currentAmountFlat = 0;
        data.filter(flat => {
            for(let param in this.filter) {
                if(+flat['sale'] !== 1 || !this.openHouses.includes(+flat[this.nameFilterFlat.house])) return;
                if(
                    this.filterName.checkbox.includes(param) &&
                    this.filter[param].value.length > 0 &&
                    !this.filter[param].value.some( key => {
                        if( +flat[this.nameFilterFlat[param]] === +key) return true;
                    })
                ) {
                    return false;
                } else if ( this.filterName.range.includes(param) ) {
                    if( +flat[this.nameFilterFlat[param]] < +this.filter[param].min ||
                        +flat[this.nameFilterFlat[param]] > +this.filter[param].max) {
                        return false;
                    }
                }
            }

            if (this.filter.house.value.length === 0 && this.filter.rooms.value.length === 0){
                return {}
            }

            if(flat[this.nameFilterFlat.house] !== undefined &&
                !this.filterSelect[flat[this.nameFilterFlat.house]]
            ) {
                this.filterSelect[flat[this.nameFilterFlat.house].match(/^(\d+)/)[1]] = [];
            }


            if (flat[this.nameFilterFlat.floor] !== undefined &&
                this.filterSelect[flat[this.nameFilterFlat.house]] &&
                !this.filterSelect[flat[this.nameFilterFlat.house]].includes(flat[this.nameFilterFlat.floor]) &&
                flat[this.nameFilterFlat.floor] > 0
            ) {
              this.filterSelect[flat[this.nameFilterFlat.house]].push(flat[this.nameFilterFlat.floor]);
            }
            this.currentAmountFlat += 1;
            return flat;
        });
      return this.filterSelect;
    }
    getFilterParam(){
        for(let key in this.filter) {
            switch ( this.filter[key].type ) {
                case 'select':
                    $('.js-s3d-filter__'+key+'--input:checked').each((i,el)=> {
                        this.filter[key].value.push( $(el).data(key) )
                    });
                    break;
                case 'range':
                    this.filter[key].min = this.filter[key].elem.result.from;
                    this.filter[key].max = this.filter[key].elem.result.to;
                    break;
            }
        }
    }
    clearFilterParam(){
        this.filterSelect = {};
        this.filter = {};
        $('#js-s3d__wrapper polygon').css({'opacity': ''});
        this.setAmountSelectFlat(this.flatList.length);
    }
}

export default Filter;
