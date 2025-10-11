const Utils = {
    init() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('[data-toggle="tooltip"]').tooltip();
        $('select.form-control:not(.select-ajax)').select2({width: '100%'});

        // Calculate width of text from DOM element or string. By Phil Freo <http://philfreo.com>
        $.fn.textWidth = function (text, font) {
            if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
            $.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
            return $.fn.textWidth.fakeEl.width();
        };

        // Set active theme
        var activeTheme = localStorage.getItem('active-theme') || 'light';
        $('body').removeClass('dark');
        $('body').addClass(activeTheme);

        $('#toggle-theme').click(function (e) {
            e.preventDefault();
            $('body').toggleClass('dark');

            // Put the object into storage
            localStorage.setItem('active-theme', $('body').hasClass('dark') ? 'dark' : 'light');
        });

        $('.toggle-switch__checkbox').on('change', function () {
            let value = $(this).is(':checked');

            if (value) {
                $(this).attr('checked', true);
            } else {
                $(this).removeAttr('checked');
            }
        });

        // Date & time pickers
        $(".datetime-picker")[0] && $(".datetime-picker").flatpickr({
            enableTime: !0,
            nextArrow: '<i class="zmdi zmdi-long-arrow-right" />',
            prevArrow: '<i class="zmdi zmdi-long-arrow-left" />',
            time_24hr: true
        });

        $(".date-picker")[0] && $(".date-picker").flatpickr({
            enableTime: !1,
            nextArrow: '<i class="zmdi zmdi-long-arrow-right" />',
            prevArrow: '<i class="zmdi zmdi-long-arrow-left" />'
        });

        $(".time-picker")[0] && $(".time-picker").flatpickr({
            noCalendar: !0,
            enableTime: !0
        });
    },

    defaultLocale() {
        return $('meta[name="default-locale"]').attr('content');
    },

    loading() {
        return `<div class="loading"><div></div><div></div><div></div><div></div></div>`;
    },

    ajax(url, method, dataType, data, options = {}) {
        return $.ajax({
            url: url,
            type: method,
            datatype: dataType,
            data: data,
            error(err) {
                // console.log('Error fetching data from ' + url, err);
            },
            ...options
        });
    },

    ajaxFile(url, method, dataType, data) {
        return $.ajax({
            url: url,
            type: method,
            datatype: dataType,
            data: data,
            error(err) {
            },
            contentType: false,
            processData: false
        });
    },

    serializeForm(form) {
        var data = form.serializeArray();
        var submit = form.find('.submit');

        $.each(submit, function () {
            var name = $(this).attr('name');
            var value = $(this).attr('value');
            var text = $(this).text();
            if (name && (value || text)) {
                data.push({name: name, value: (value) ? value : text});
            }
        });

        return data;
    },

    loadModal(size = 'md', dismissable = false, showClose = true) {

        let modalId = 'modal-' + new Date().getTime();

        let modal = `<div class="modal fade" id="${modalId}" tabindex="-1" style="display: none;" aria-hidden="true" ${!dismissable ? 'data-backdrop="static"' : ''}>
            <div class="modal-dialog modal-${size}">
                <div class="modal-content">
                    ${showClose ? '<button type="button" class="close modal-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>' : ''}
                    <div class="modal-header">
                        <h5 class="modal-title pull-left">Default modal</h5>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

        $('body').append(modal);

        $('#' + modalId).modal('show')
            .on('hidden.bs.modal', function (e) {
                $(this).remove();
            });

        return $('#' + modalId);
    },

    enable(element) {
        element.attr('disabled', false)
            .find('span')
            .remove();

        element.html(element.data('text'));
    },

    disable(element) {
        element.attr('data-text', element.html())
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>')
            .attr('disabled', true);
    },

    disableSubmit(form) {
        let submit = form.find('.btn--submit');
        let width = submit.outerWidth();
        let text = submit.html();
        let isGroupedButton = submit.hasClass('btn-group');

        if (!isGroupedButton) {
            submit.attr('data-text', text)
                .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>')
                .attr('disabled', true)
                .css('min-width', width);
        } else {
            submit.attr('data-text', text)
                .find('button').eq(0)
                .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>')
                .attr('disabled', true);
        }
    },

    enableSubmit(form) {
        let submit = form.find('.btn--submit');
        let text = submit.data('text');

        submit.attr('disabled', false)
            .find('span')
            .remove();

        submit.html(text);
    },

    getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    },

    alertWithMessage(title, messages, type) {

        let html = '';

        if (typeof messages === 'object') {
            messages.forEach(message => {
                html += `<p class="has-text-danger">${message}</p>`;
            });
        } else {
            html = messages;
        }

        Swal({
            title,
            html,
            type,
            heightAuto: false
        });
    },

    alert(title, messages, type) {

        let html = '';

        if (typeof messages === 'object') {
            messages.forEach(message => {
                html += `<p class="has-text-danger">${message}</p>`;
            });
        } else {
            html = messages;
        }

        Swal({
            title,
            html,
            type,
            confirmButtonText: 'Okay',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-secondary',
            buttonsStyling: false
        });
    },

    formFailed(form, message) {
        Utils.enableSubmit(form);
        Utils.alert('Failed', message, 'error');
    },

    capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');
    },

    titleToSlug(text) {
        return text.toString()
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    },

    datatable: function (element, options, buttons = '') {
        let dataTableButtons = buttons ? buttons : `
            <div class="dataTables_buttons actions">
                <span class="actions__item zmdi zmdi-plus" data-table-action="create" title="" data-toggle="tooltip" data-placement="top" data-original-title="Create Record"></span>
                <span class="actions__item zmdi zmdi-refresh" data-table-action="reload" title="" data-toggle="tooltip" data-placement="top" data-original-title="Reload"></span>
            </div>`;

        return $(element).DataTable($.extend({
            processing: true,
            serverSide: true,
            autoWidth: false,
            responsive: true,
            lengthMenu: [[15, 30, 45], ['15 Rows', '30 Rows', '45 Rows']],
            order: [[0, "desc"]],
            language: {
                searchPlaceholder: "Search for records...",
                processing: '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>'
            },
            dom: 'Blfrtip',
            initComplete: function (settings, json) {
                $(this).closest('.dataTables_wrapper')
                    .prepend(dataTableButtons)
                    .find('[data-toggle="tooltip"]')
                    .tooltip({
                        delay: {
                            show: 650
                        }
                    });
            }
        }, options));
    },

    datepicker(element) {
        $(element).flatpickr({
            enableTime: false,
            dateFormat: "d-m-Y",
            nextArrow: '<i class="zmdi zmdi-long-arrow-right" />',
            prevArrow: '<i class="zmdi zmdi-long-arrow-left" />'
        });
    },

    timepicker(element) {
        $(element).flatpickr({
            noCalendar: true,
            enableTime: true,
            time_24hr: true
        });
    },

    daterangepicker(element, options, callback = null) {
        $(element).daterangepicker($.extend({
            startDate: moment().subtract(7, 'days'),
            endDate: moment().subtract(1, 'days'),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(7, 'days'), moment().subtract(1, 'days')],
                'Last 30 Days': [moment().subtract(30, 'days'), moment().subtract(1, 'days')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, options), callback);
    },

    select2(element, options = {}) {
        $(element).select2($.extend({
            dropdownAutoWidth: true,
            width: "100%",
        }, options));
    },

    notify(title, message, type = 'inverse', url = '') {
        $.notify({
            icon: 'zmdi zmdi-user',
            title: title,
            message: message,
            url: url
        }, {
            element: 'body',
            type: type,
            allow_dismiss: true,
            placement: {
                from: 'bottom',
                align: 'center'
            },
            offset: {
                x: 15, // Keep this as default
                y: 15  // Unless there'll be alignment issues as this value is targeted in CSS
            },
            spacing: 10,
            z_index: 1031,
            delay: 4000,
            timer: 1000,
            url_target: '_blank',
            mouse_over: false,
            animate: {
                enter: 'animated fadeIn',
                exit: 'animated fadeOut'
            },
            template: `<div data-notify="container" class="alert alert-dismissible alert-{0} alert--notify" role="alert">
                <span data-notify="icon"></span>
                <span data-notify="title">{1}</span>
                <span data-notify="message">{2}</span>
                <div class="progress" data-notify="progressbar">
                    <div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                </div>
                <a href="{3}" target="{4}" data-notify="url"></a>
                <button type="button" aria-hidden="true" data-notify="dismiss" class="alert--notify__close">Close</button>
            </div>`
        });
    },

    baseUrl(path = '') {
        return `${$('meta[name="base-url"]').attr('content')}/${path}`;
    },

    keditorCustomComponents() {

        let KEditor = $.keditor;
        let flog = KEditor.log;

        KEditor.components['photo'] = {
            init: function (contentArea, container, component, keditor) {
                flog('init "photo" component', component);

                var componentContent = component.children('.keditor-component-content');
                var img = componentContent.find('img');

                img.css('display', 'inline-block');
            },

            settingEnabled: true,

            settingTitle: 'Photo Settings',

            initSettingForm: function (form, keditor) {
                flog('initSettingForm "photo" component');

                var self = this;
                var options = keditor.options;

                form.append(
                    '<form class="form-horizontal">' +
                    '   <div class="form-group">' +
                    '       <div class="col-sm-12">' +
                    '           <button type="button" class="btn btn-block btn-primary" id="photo-edit">Change Photo</button>' +
                    '           <input type="file" style="display: none" />' +
                    '       </div>' +
                    '   </div>' +
                    '   <div class="form-group">' +
                    '       <label for="photo-align" class="col-sm-12">Align</label>' +
                    '       <div class="col-sm-12">' +
                    '           <select id="photo-align" class="form-control">' +
                    '               <option value="left">Left</option>' +
                    '               <option value="center">Center</option>' +
                    '               <option value="right">Right</option>' +
                    '           </select>' +
                    '       </div>' +
                    '   </div>' +
                    '   <div class="form-group">' +
                    '       <label for="photo-responsive" class="col-sm-12">Responsive</label>' +
                    '       <div class="col-sm-12">' +
                    '           <input type="checkbox" id="photo-responsive" />' +
                    '       </div>' +
                    '   </div>' +
                    '   <div class="form-group">' +
                    '       <label for="photo-width" class="col-sm-12">Width</label>' +
                    '       <div class="col-sm-12">' +
                    '           <input type="number" id="photo-width" class="form-control" />' +
                    '       </div>' +
                    '   </div>' +
                    '   <div class="form-group">' +
                    '       <label for="photo-height" class="col-sm-12">Height</label>' +
                    '       <div class="col-sm-12">' +
                    '           <input type="number" id="photo-height" class="form-control" />' +
                    '       </div>' +
                    '   </div>' +
                    '</form>'
                );

                var photoEdit = form.find('#photo-edit');
                var fileInput = photoEdit.next();
                photoEdit.on('click', function (e) {
                    e.preventDefault();

                    let origin = $(this);
                    let modal = Utils.loadModal('full', 'none');
                    let title = modal.find('.modal-title');
                    let body = modal.find('.modal-body');

                    title.text('Add Featured Image');

                    Utils.ajax('/media/image-picker', 'GET', 'text/html', null)
                        .then(results => {
                            body.html(results);
                            Media.imagePicker();
                        });

                    modal.on('click', '.media-choose', function () {
                        let element = $(this);
                        let url = element.data('url');
                        let id = element.data('id');

                        let img = keditor.getSettingComponent().find('img');

                        img.attr('src', url);
                        img.css({
                            width: '',
                            height: ''
                        });

                        modal.modal('hide');
                    });

                });

                var inputAlign = form.find('#photo-align');
                inputAlign.on('change', function () {
                    var panel = keditor.getSettingComponent().find('.photo-panel');
                    panel.css('text-align', this.value);
                });

                var inputResponsive = form.find('#photo-responsive');
                inputResponsive.on('click', function () {
                    keditor.getSettingComponent().find('img')[this.checked ? 'addClass' : 'removeClass']('img-responsive');
                });

                var inputWidth = form.find('#photo-width');
                var inputHeight = form.find('#photo-height');
                inputWidth.on('change', function () {
                    var img = keditor.getSettingComponent().find('img');
                    var newWidth = +this.value;
                    var newHeight = Math.round(newWidth / self.ratio);

                    if (newWidth <= 0) {
                        newWidth = self.width;
                        newHeight = self.height;
                        this.value = newWidth;
                    }

                    img.css({
                        'width': newWidth,
                        'height': newHeight
                    });
                    inputHeight.val(newHeight);
                });
                inputHeight.on('change', function () {
                    var img = keditor.getSettingComponent().find('img');
                    var newHeight = +this.value;
                    var newWidth = Math.round(newHeight * self.ratio);

                    if (newHeight <= 0) {
                        newWidth = self.width;
                        newHeight = self.height;
                        this.value = newHeight;
                    }

                    img.css({
                        'height': newHeight,
                        'width': newWidth
                    });
                    inputWidth.val(newWidth);
                });
            },

            showSettingForm: function (form, component, keditor) {
                flog('showSettingForm "photo" component', component);

                var self = this;
                var inputAlign = form.find('#photo-align');
                var inputResponsive = form.find('#photo-responsive');
                var inputWidth = form.find('#photo-width');
                var inputHeight = form.find('#photo-height');

                var panel = component.find('.photo-panel');
                var img = panel.find('img');

                var algin = panel.css('text-align');
                if (algin !== 'right' || algin !== 'center') {
                    algin = 'left';
                }

                inputAlign.val(algin);
                inputResponsive.prop('checked', img.hasClass('img-responsive'));
                inputWidth.val(img.width());
                inputHeight.val(img.height());

                $('<img />').attr('src', img.attr('src')).load(function () {
                    self.ratio = this.width / this.height;
                    self.width = this.width;
                    self.height = this.height;
                });
            }
        };
    },

    renderLocales(field) {
        let locales = $('meta[name="locales"]').attr('content');
        locales = locales.split(',');

        return locales.map(item => {
            return {
                name: `${field}->${item}`,
                data: `${field}.${item}`,
                render: function (data, type, row, meta) {
                    return row[field][item] || '';
                }
            }
        });
    },
};

export default Utils;
