jQuery(document).ready(function () {
    jQuery('#mod_siwecos_login_button').click(function () {
        var username = jQuery('#mod_siwecos_uname').val();
        var password = jQuery('#mod_siwecos_pwd').val();
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_ajax&module=siwecos&method=login&format=json',
            data: {uname: username, pwd: password},
            success: function (responseData) {
                if (responseData.data.code == '200') {
                    location.reload();
                } else {
                    alert(responseData.data.message);
                }
            },
            error: function () {
                alert("SIWECOS API error");
            }
        });
    });

    if(jQuery('#mod_siwecos_results').length) {
        jQuery.ajax({
            type: 'GET',
            url: 'index.php?option=com_ajax&module=siwecos&method=domainStatus&format=json',
            success: function (responseData) {
                console.log(responseData);
                if (responseData.data.code == '200') {
                    jQuery('#mod_siwecos_loadingtext').remove();
                    jQuery('#mod_siwecos_resultscale').attr('data-value', parseInt(responseData.data.result.value));
                    jQuery('#mod_siwecos_resultneedle').css({'transform': 'rotate(' + ( 180 * ( responseData.data.result.value / 100 ) ) + 'deg)'});

                    var scanners = "";

                    jQuery.each(responseData.data.result.scanners, function(index, scanner) {
                        var badgeClass = "badge-success";

                        if(scanner.value < 66) {
                            badgeClass = "badge-warning";
                        }

                        if(scanner.value < 33) {
                            badgeClass = "badge-important";
                        }

                        scanners = scanners + '<div class="row-fluid"><span class="badge ' + badgeClass +'">' + scanner.value + ' / 100</span> ' + scanner.name + ' <span class="small">' + scanner.description + '</span></div>';
                    });

                    jQuery('#mod_siwecos_scannerlist').html(scanners);
                    jQuery('#mod_siwecos_results').show();

                } else {
                    jQuery('#mod_siwecos_loadingtext').remove();
                    jQuery('#mod_siwecos_results').text(Joomla.JText._('MOD_SIWECOS_RESULTS_DOMAIN_NOT_FOUND'));
                    jQuery('#mod_siwecos_results').show();
                }
            },
            error: function () {
                alert("SIWECOS API error");
            }
        });
    }
});
