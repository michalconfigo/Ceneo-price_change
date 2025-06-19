/*global woocommerce_admin_meta_boxes */

jQuery(function($){

    var categoryToBeAssigned = null;

    $('.indent input[type=checkbox]').on('click', function(){
       if($(this).is(':checked')) {
           $(this).prev().prop('disabled', 0);
       } else {
           $(this).prev().prop('disabled', 1);
       }
    });

    $(window).on('click', function(){
        categoryToBeAssigned = null;
        $('.drop-category').removeClass('assignment-active');
    });

    $('.drop-category').on('click', function(event) {
        event.stopPropagation();
        if(!$(this).hasClass('ui-state-assigned')) {
            $(this).addClass('assignment-active');
            categoryToBeAssigned = $(this);
            console.log('Clicked');
        }
    });

    $('.draggable-category').on('click', function() {
        if(categoryToBeAssigned) {
            setCategory(categoryToBeAssigned, $(this));
            $('.drop-category').removeClass('assignment-active');
            categoryToBeAssigned = null;
        }
    });

    $('input.auto-match').on('click', function(e) {
        e.preventDefault();
        $('.woocommerce-categories .category-item .item').each(function() {
            $categoryName = $(this).find('.category-name').text();
            $droppable = $(this).find('.drop-category');
            $draggable = $('[cat-search="' + $categoryName.toLowerCase() + '"]');
            if($droppable.length === 1 && $draggable.length === 1) {
                setCategory($droppable, $draggable);
            }
        }).parent().addClass('expanded');
    });

    $("#category-search").on('change', function() {
        $value = $(this).val();
        if($value === '') {
            $('.ceneo-categories li').removeClass('expanded').show();
        } else {
            $('.ceneo-categories li').hide();
            $('[cat-search*="' + $(this).val().toLowerCase() + '"]').parents('li').show().addClass('expanded');
        }
    });

    $(".toolbar-ceneo a").on("click", function(event) {

        let $dataAttributes = JSON.parse($(this).attr('data-attributes'));

        let size = $('.product_attributes .woocommerce_attribute').length;

        let $button = $(this);
        let $wrapper = $(this).closest('#product_attributes');
        let $attributes = $wrapper.find('.product_attributes');

        $dataAttributes.forEach(function(elem, i) {
            var product_type = $('select#product-type').val();
            var data = {
                action: 'woocommerce_add_attribute',
                taxonomy: '',
                i: size + i,
                security: woocommerce_admin_meta_boxes.add_attribute_nonce
            };

            $wrapper.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
                $attributes.append(response);

                if ('variable' !== product_type) {
                    $attributes.find('.enable_variation').hide();
                }

                $(document.body).trigger('wc-enhanced-select-init');

                $('.product_attributes .woocommerce_attribute').each(function (index, el) {
                    $('.attribute_position', el).val(parseInt($(el).index('.product_attributes .woocommerce_attribute'), 10));
                });

                let currentMetaBox = $attributes.find('.woocommerce_attribute').last();
                currentMetaBox.find('h3 .attribute_name').text(elem);
                currentMetaBox.find('.wc-metabox-content input.attribute_name').val(elem).attr('readonly', 'readonly');

                $wrapper.unblock();
                if(i === $dataAttributes.length - 1) {
                    $button.parent().text("Atrybuty zostały dodane. Uzupełnij wartości poniżej.");
                }

                $(document.body).trigger('woocommerce_added_attribute');
            });
        });
        return false;
    });

    // Display progressbar
    $( "#progressbar" ).progressbar({
        value: +$("#progressbar").attr("data-value")
    });

    // Expand accordion
    $(".simple-accordion h4").on('click', function(event){
        if($(this).hasClass('clickable')) {
            event.stopPropagation();
        }
        $(this).parent().parent().toggleClass('expanded');
    }) ;

    // Make category bar draggable
    $( ".draggable-category" ).draggable({
        helper: "clone",
        revert: 'invalid',
    });

    // Toggle attributes
    $( ".toggle-attributes").on('click', function(event){
        event.preventDefault();
       $(this).parent().toggleClass('expanded');
    });

    $( ".drop-category" ).on('click', '.remove', function(event){
        event.stopPropagation();
        // Clear drop category field
        let dropCategory = $(this).parent().parent();
        dropCategory.empty();
        dropCategory.removeClass('ui-state-assigned');
        // Clear select fields
        let dummyOption = document.createElement('option');
        dummyOption.setAttribute('value', null);
        dummyOption.setAttribute('disabled', 'disabled');
        dummyOption.setAttribute('selected', 'selected');
        dummyOption.innerText = '--';
        dropCategory.siblings('.attributes').find('select').empty().append(dummyOption);
        dropCategory.siblings('.attributes').find('select').prop('disabled', 1);
        dropCategory.siblings('input').val(null);
        // Toggle expanded
        dropCategory.parent().removeClass('expanded');
    });

    // Make category bar droppable
    $( ".drop-category" ).droppable({
        tolerance: 'pointer',
        accept: function(draggable) {
            if(draggable.hasClass('draggable-category')){
                return true;
            }},
        drop: function( event, ui ) {
            setCategory($(this), ui.draggable);
        }
    });
});

function setCategory(elem, draggable) {
    elem.addClass( "ui-state-assigned" );

    // Expand attributes after assigning category
    elem.parent().addClass('expanded');
    //$( this ).parent().parent().children('.woocommerce-attributes').attr('cat-id', ui.draggable.attr('cat-id'));

    // Add visual element with value
    let newElem = document.createElement("h4");
    newElem.setAttribute('cat-id', draggable.attr('cat-id'));
    newElem.innerText = draggable.attr('cat-name');
    let span = document.createElement('span');
    span.setAttribute('class', 'material-icons-outlined remove');
    span.innerText = 'close';
    newElem.append(span);

    // Set value of form field
    elem.siblings('input').val(draggable.attr('cat-id'));
    elem.empty().append(newElem);

    //Populate dropdown with attributes
    let options = [];
    let defaultOption = document.createElement('option');
    defaultOption.setAttribute('value', null);
    defaultOption.innerText = 'Wybierz atrybut';
    options.push(defaultOption);

    let dummyOption = document.createElement('option');
    dummyOption.setAttribute('value', null);
    dummyOption.setAttribute('disabled', 'disabled');
    dummyOption.innerText = '--';
    options.push(dummyOption);

    let attributes = JSON.parse(draggable.attr('cat-optional-attributes'));
    if(attributes) {
        attributes.forEach((elem) => {
            let option = document.createElement('option');
            option.setAttribute('value', elem);
            option.innerText = elem;
            options.push(option);
        });
    }

    elem.siblings('.attributes').find('select').empty().append(options).removeAttr('disabled');
}
