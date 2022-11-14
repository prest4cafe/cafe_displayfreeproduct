prestashop.on('updatedProduct', calculate);
 
 function calculate (){

    var currentprice = $(".current-price-value").attr("content");

    if (currentprice == 0) {

        $(".current-price-value").replaceWith(free);


    }

}