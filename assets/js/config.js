(function($){
    $('button.shipping_vendesfacil_update_cities').click(function(e){
        e.preventDefault();
        $.ajax({
            method: 'GET',
            url: ajaxurl,
            data: {action: 'shipping_vendesfacil_wc_svwc'},
            beforeSend: function(){
                Swal.fire({
                    title: 'Actualizando',
                    onOpen: () => {
                        swal.showLoading()
                    },
                    allowOutsideClick: false
                });
            },
            success: function(){
                Swal.fire({
                    title: 'Se ha actualizado exitosamente',
                    text: 'redireccionando a configuraciones...',
                    type: 'success',
                    showConfirmButton: false
                });
                window.location.replace(shippingVendesFacil.urlConfig);
            }
        });
    });
})(jQuery);