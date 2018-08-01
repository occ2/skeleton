(function($, undefined){
    $.nette.ext('autorefresh',{
        init:   function(){
            $(this.selector).each(function(index){
                var url = $(this).data("refresh-url");
                var time = $(this).data("refresh-time")*1000;
                setInterval(function(url) {
                    this.disableSpinner = true;
                    $.nette.ajax(url);
                }, time, url);
            });
        },
        load:  function(){
            if(this.disableSpinner===true){
                $("#ajax-spinner").hide();
            }
        }
    },{
        selector:    ".autorefresh",
        disableSpinner: false
    });
})(jQuery);
