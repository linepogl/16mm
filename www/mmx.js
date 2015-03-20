

MMX = {

    ShowTab:function(tab){
        jQuery('.mmx-tab').removeClass('active');
        jQuery('#mmx-tab-'+tab).addClass('active');
        jQuery('.mmx-tab-page').hide();
        jQuery('#mmx-tab-page-'+tab).show();
    }

    ,
    ShowCategory:function(category){
        jQuery('.mmx-category').removeClass('active');
        jQuery('#mmx-category-'+category).addClass('active');

    }



};