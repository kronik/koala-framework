Kwf.onContentReady(function(readyEl, param) {
    if (!param.newRender) return false;
    Ext.select('.kwcForm', true, readyEl).each(function(form) {
        if (form.shopBoxCartInitDone) return;
        form.shopBoxCartInitDone = true;
        form.kwcForm.on('submitSuccess', function(r) {
            Ext.select('.kwcShopBoxCart').each(function(el) {
                var url = '/kwf/util/kwc/render';
                if (Kwf.Debug.rootFilename) url = Kwf.Debug.rootFilename + url;
                Ext.Ajax.request({
                    params: { componentId: el.dom.id },
                    url: url,
                    success: function(response, options) {
                        this.replaceWith({ html: response.responseText });
                        Kwf.callOnContentReady();
                    },
                    scope: el
                });

            }, this);
        }, this);
    });
}, window, { priority: 10 }); //call after Kwc.Form.Component