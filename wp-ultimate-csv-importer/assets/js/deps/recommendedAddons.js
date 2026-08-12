jQuery(document).ready(function(){
    document.getElementById('click_get_started').onclick = function () { 
      jQuery(this).html('<img src = ' + `${window.smack_nonce_object.imagePath}ajax-loader.gif`+ '>');
        var addons = ['Users','WooCommerce','Exporter'];                     
            jQuery.ajax({
              type: 'POST',
              url: smack_nonce_object.url,
              data: {
                'action' : 'install_plugins',
                'addons' : addons,                
                'securekey' : smack_nonce_object.nonce,
              },

              success: function(data){                   
                window.location.replace(window.location.origin + ajaxurl+ '/../admin.php?page=com.smackcoders.csvimporternew.menu');
              },
              error: function(errorThrown){
              }
            });          
    }

    $("#Useraddon").click(function() {     
      jQuery(this).html('<img src = ' + `${window.smack_nonce_object.imagePath}ajax-loader.gif`+ '>'); 
      jQuery.ajax({
        type: 'POST',
        url: smack_nonce_object.url,
        data: {
          'action' : 'install_addon',
          'addons' : 'Users',
          'securekey' : smack_nonce_object.nonce,
        },

        success: function(response){          
          $("#Useraddon").html('<span class="check-icon">✓</span> Activated').addClass('wpucsv-btn-active').prop('disabled', true);               
        },
        error: function(errorThrown){
        }
      });
  });

  $("#WooCommerceaddon").click(function() {   
    jQuery(this).html('<img src = ' + `${window.smack_nonce_object.imagePath}ajax-loader.gif`+ '>'); 
    jQuery.ajax({
      type: 'POST',
      url: smack_nonce_object.url,
      data: {
        'action' : 'install_addon',
        'addons' : 'WooCommerce',
        'securekey' : smack_nonce_object.nonce,
      },

      success: function(response){       
        $("#WooCommerceaddon").html('<span class="check-icon">✓</span> Activated').addClass('wpucsv-btn-active').prop('disabled', true);           
      },
      error: function(errorThrown){
      }
    });
});

$("#Exporteraddon").click(function() {  
  jQuery(this).html('<img src = ' + `${window.smack_nonce_object.imagePath}ajax-loader.gif`+ '>');
  jQuery.ajax({
    type: 'POST',
    url: smack_nonce_object.url,
    data: {
      'action' : 'install_addon',
      'addons' : 'Exporter',
      'securekey' : smack_nonce_object.nonce,
    },

    success: function(response){             
      $("#Exporteraddon").html('<span class="check-icon">✓</span> Activated').addClass('wpucsv-btn-active').prop('disabled', true);          
    },
    error: function(errorThrown){
    }
  });
});
    
});

