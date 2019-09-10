jQuery(document).ready(function () {

    if(jQuery('#mod_siwecos_results').length) {
        jQuery.ajax({
            type: 'GET',
            url: 'index.php?option=com_ajax&plugin=siwecos&group=system&method=domainStatus&format=json',
            success: function (responseData) {
                if (responseData.success == true) {
                    jQuery('#mod_siwecos_loadingtext').remove();
                    jQuery('#mod_siwecos_resultscale').attr('data-value', parseInt(responseData.data[0].result.weightedMedia));
                    jQuery('#mod_siwecos_resultneedle').css({'transform': 'rotate(' + ( 180 * ( responseData.data[0].result.weightedMedia / 100 ) ) + 'deg)'});

                    var scanners = "";

                    jQuery.each(responseData.data[0].result.scanners, function(index, scanner) {
                        var badgeClass = "badge-success";

                        if(scanner.score < 66) {
                            badgeClass = "badge-warning";
                        }

                        if(scanner.score < 33) {
                            badgeClass = "badge-important";
                        }

                        scanners = scanners + '<div class="row-fluid"><span class="badge ' + badgeClass +'" style="width: 50px; text-align: center">' + parseInt(scanner.score) + ' / 100</span> ' + scanner.scanner_type + '</div>';
                    });

                    jQuery('#mod_siwecos_scannerlist').html(scanners);
                    jQuery('#mod_siwecos_results').show();

                    jQuery("#mod_siwecos_results .GaugeMeter").data('percent', parseInt(responseData.data[0].result.weightedMedia));
                    jQuery("#mod_siwecos_results .GaugeMeter").gaugeMeter();
                    jQuery("#mod_siwecos_results #siwecosLastScan").html(responseData.data[0].result.scanFinished.localDate);

                } else {
                    jQuery('#mod_siwecos_loadingtext').remove();
                    jQuery('#mod_siwecos_results').text(Joomla.JText._(responseData.message));
                    jQuery('#mod_siwecos_results').show();
                }
            },
            error: function () {
                alert("SIWECOS API error");
            }
        });
    }

    jQuery('#siwecosStartScanBtn').click(function(e) {
        jQuery.ajax({
            type: 'GET',
            url: 'index.php?option=com_ajax&plugin=siwecos&group=system&method=domainScan&format=json',
            success: function (responseData) {
                jQuery(e.currentTarget).html(Joomla.JText._('MOD_SIWECOS_SCAN_STARTED'))
            },
            error: function () {
                alert("SIWECOS API error");
            }
        });
    });
});
