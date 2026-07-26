(function initAjaxPagging(retries) {
    if (typeof window.jQuery === 'undefined') {
        if (retries > 0) {
            setTimeout(function () {
                initAjaxPagging(retries - 1);
            }, 50);
        }
        return;
    }

    var $ = window.jQuery;

    $(document).ready(function () {
        $(document).on('click', '.ajshort a', function () {
            var thisHref = $(this).attr('href');
            thisHref = decodeURIComponent(thisHref);
            if (!thisHref) {
                return false;
            }
            $('#loaderID').show();
            $('#listID').load(thisHref, function () {
                $(this).fadeTo(200, 1);
            });
            return false;
        });
    });

    $(document).on('click', '.admin_ajax_search', function () {
        var thisHref = $(location).attr('href');
        thisHref = decodeURIComponent(thisHref);
        $('#loaderID').show();
        $.ajax({
            type: 'POST',
            url: thisHref,
            cache: false,
            data: $('#adminSearch').serialize(),
            success: function (result) {
                $('#listID').html(result);
            }
        });
        return false;
    });

    window.ajaxSearch = function ajaxSearch() {
        var thisHref = $(location).attr('href');
        thisHref = decodeURIComponent(thisHref);
        $('#loaderID').show();
        $.ajax({
            type: 'GET',
            url: thisHref,
            cache: false,
            data: $('#adminSearch').serialize(),
            success: function (result) {
                $('#listID').html(result);
            }
        });
        return false;
    };

    window.actionFromAjax = function actionFromAjax() {
        var thisHref = $(location).attr('href');
        $('#loaderID').show();
        $.ajax({
            type: 'POST',
            url: thisHref,
            cache: false,
            data: $('#actionFrom').serialize(),
            success: function (result) {
                $('#listID').html(result);
            }
        });
        return false;
    };

    window.ajaxActionFunction = function ajaxActionFunction() {
        if (isAnySelect()) {
            actionFromAjax();
        }
        return false;
    };

    $(document).ready(function () {
        // If a previous AJAX status toggle left inline display:block on loader, reset it.
        $('.right_action_lo').hide();

        $(document).on('click', '.right_acdc', function (e) {
            var clickId = this.id;
            var clickTitle = $(this).children('a').attr('title');
            var thisHref = $('#' + clickId).find('a').attr('href');
            if (thisHref != 'javascript:void(0)') {
                if (!confirm('Are you sure you want to ' + clickTitle + ' ?')) {
                    e.preventDefault();
                    return false;
                }

                $('#loder' + clickId).show();
                // Safety net: if the AJAX call hangs, refresh to reflect server-side change.
                var fallbackReload = setTimeout(function () {
                    window.location.reload();
                }, 10000);

                $.ajax({
                    type: 'GET',
                    url: thisHref,
                    cache: false,
                    timeout: 9000,
                    success: function (result) {
                        clearTimeout(fallbackReload);
                        $('#' + clickId).html(result);
                        // Some responses can be incomplete/errored while status already changed.
                        // Force a quick refresh so the list reflects final state consistently.
                        window.location.reload();
                    },
                    error: function () {
                        clearTimeout(fallbackReload);
                        window.location.reload();
                    },
                    complete: function () {
                        $('#loder' + clickId).hide();
                        $('.right_action_lo').hide();
                    }
                });
                return false;
            }
        });
    });
}(100));
