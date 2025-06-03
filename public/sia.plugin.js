
function siaOps(){
    this.setAttribute = function(name, value){
        if(this.attributes.hasOwnProperty(name)){
            delete this.attributes[name];
        }
        if(typeof value === 'function'){
            this.attributes[name] = value.bind(this);
            this.attributes[name] = this.attributes[name]();
        }else{
            this.attributes[name] = value;
        }
        return this;
    }

    this.getAttribute = function(name){
        if(this.attributes.hasOwnProperty(name)){
            return this.attributes[name];
        }
        return null;
    }

    this.addModules = function(name, callback){
        if(this.modules.hasOwnProperty(name)){
            delete this.modules[name];
            this.modules[name] = callback;
        }else{
            this.modules[name] = callback;
        }
        return this;
    }

    this.addMethods = function(name, callback){
        if(this.methods.hasOwnProperty(name)){
            delete this.methods[name];
            this.methods[name] = callback;
        }
        this.methods[name] = callback;
        return this;
    }

    this.callMethod = function(methodName){
        if(this.methods.hasOwnProperty(methodName)){
            let callable = this.methods[methodName].bind(this);
            callable()
        }
    }

    this.callModule = function(moduleName){
        if(this.modules.hasOwnProperty(moduleName)){
            let callable = this.modules[moduleName].bind(this);
            return callable;
        }
        return null;
    }

    this.loadScript = function(src, options = {}) {
        const config = {
            position: 'body',
            async: true,
            defer: false,
            callback: null,
            dependencies: [],
            ...options
        };

        const scripts = Array.isArray(src) ? src : [src];
        const allScripts = [...config.dependencies, ...scripts];

        const loadPromises = allScripts.map(script => {
            return new Promise((resolve, reject) => {
                // Cek apakah script sudah dimuat sebelumnya
                if (document.querySelector(`script[src="${script}"]`)) {
                    resolve();
                    return;
                }

                const scriptEl = document.createElement('script');
                scriptEl.src = script;
                scriptEl.async = config.async;
                scriptEl.defer = config.defer;
                scriptEl.onload = resolve;
                scriptEl.onerror = reject;

                // Tambahkan ke DOM
                if (config.position === 'head') {
                    document.head.appendChild(scriptEl);
                } else {
                    document.body.appendChild(scriptEl);
                }
            });
        });

        // Gabungkan semua promise
        return Promise.all(loadPromises)
            .then(() => {
                if (config.callback && typeof config.callback === 'function') {
                    config.callback.call(this);
                }
                return true;
            })
            .catch(error => {
                console.error('Script loading failed:', error);
                throw error;
            });
    };

    this.loadCSS = function(href, options = {}) {
        const config = {
            position: 'head',
            callback: null,
            ...options
        };

        const stylesheets = Array.isArray(href) ? href : [href];

        const loadPromises = stylesheets.map(style => {
            return new Promise((resolve, reject) => {
                if (document.querySelector(`link[href="${style}"]`)) {
                    resolve();
                    return;
                }

                const linkEl = document.createElement('link');
                linkEl.rel = 'stylesheet';
                linkEl.href = style;
                linkEl.onload = resolve;
                linkEl.onerror = reject;

                document[config.position].appendChild(linkEl);
            });
        });

        return Promise.all(loadPromises)
            .then(() => {
                if (config.callback && typeof config.callback === 'function') {
                    config.callback.call(this);
                }
                return true;
            });
    };


}

siaOps.prototype.attributes = {};
siaOps.prototype.modules = {};
siaOps.prototype.methods = {};
window.SIAOPS = new siaOps;

function formatTimeAgo(timestamp) {
    const now = new Date();
    const time = new Date(timestamp); // Konversi string ke objek Date
    if (isNaN(time.getTime())) {
        return "Tanggal tidak valid"; // Validasi jika format tidak benar
    }

    const diff = now - time;

    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (seconds < 60) {
        return "Baru saja";
    } else if (minutes < 60) {
        return `${minutes} menit yang lalu`;
    } else if (hours < 24) {
        return `${hours} jam yang lalu`;
    } else if (days <= 3) {
        return `${days} hari yang lalu`;
    } else {
        return time.toLocaleDateString("id-ID", {
            year: "numeric",
            month: "long",
            day: "numeric",
            // hour: "2-digit",
            // minute: "2-digit",
        });
    }
}

function notificationPanel(){
    this.notifIncrement = 0;

    this.actionNotification = function(id, url){
        let url_update = $('#panel-notification').data('url-notification-redirect');
        window.location.href = url_update+'/'+id;
    }

    this.reloadNotification = function(){
        var url = $('#panel-notification').data('url-notification');
        var notif = this;
        $.ajax({
            url: url,
            type: 'POST',
            data: {},
            dataType: 'json',
            success: function(data) {
                var strNotif = ``;
                data.list.forEach((val, index) => {
                    strNotif += `
                        <div class="dropdown-item" onclick="notificationPanel.actionNotification(${val.id}, '${val.url}')" data-url-action="${val.url}" style="${(val.is_read === "0") ? 'background: #fff3cd !important;':''}">
                            <span class="icon bg-success p-2 text-white btn-round"><i class="fa fa-comment"></i></span>
                            <div class="content-wrapper">
                                <div class="content">${val.title}</div>
                                <div class="time">${formatTimeAgo(val.created_at)}</div>
                            </div>
                        </div>
                    `;
                })
                $('#notiv-body').html(strNotif);
                notif.notifIncrement = data.total_notif_no_read;
                // $('#title-new-notification').html(`You have ${notif.notifIncrement} new notification`);
                $('.notification').html(notif.notifIncrement);
                if(notif.notifIncrement > 0){
                    $('#title-page').html(`(${notif.notifIncrement}) PT Data Utama Dinamika`);
                }
            },
            error: function(xhr, status, error) {
                alert('Cannot load data account contact');
            }
        });
    }
    this.updateNotification = function(message){
        this.notifIncrement++;
        // $('#title-new-notification').html(`You have ${this.notifIncrement} new notification`);
        $('.notification').html(this.notifIncrement);
        $('#title-page').html(`(${this.notifIncrement}) PT Data Utama Dinamika`);
        $('#notiv-body').prepend(`
            <div class="dropdown-item" onclick="notificationPanel.actionNotification(${message.data.id}, '${message.data.url}')" data-url-action="${message.data.url}" style="background: #fff3cd;">
                <span class="icon bg-success p-2 text-white btn-round"><i class="fa fa-comment"></i></span>
                <div class="content-wrapper">
                    <div class="content">${message.notification.title}</div>
                    <div class="time">${formatTimeAgo(new Date())}</div>
                </div>
            </div>
        `);
    }

    this.triggerBtnNotification = function(data_check){
        if(data_check.length > 0){
            $('#unread-btn').show();
            $('#read-btn').show();
            $('#unread-btn').html(`<i class="fa fa-eye-slash"></i> (${data_check.length}) Unread`);
            $('#read-btn').html(`<i class="fa fa-eye"></i> (${data_check.length}) Read`);
        }else{
            $('#unread-btn').hide();
            $('#read-btn').hide();
        }
    }
}
