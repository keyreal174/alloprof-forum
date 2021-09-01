function AlloprofForumApp () {
    var self = this;
    self.callbacks = [];
    self.ObserverReady= function (app, obs) {
        self.app = app;
        self.obs = obs;

        for (var ku = 0; ku < self.callbacks.length; ku ++) {
            self.callbacks[ku](self);
        }
        self.callbacks = [];
        
    }

    self.initializeObserverLink= function() {
        window['Observables'] = window['Observables'] || {
            apps: []
        };

        window['Observables'].apps.push({
            instance: self,
            name: 'forum'
        })
    }

    self.initializeObserverLink();

    self.onReady = function(callback) {
        if (self.obs) {
            callback(self);
        } else {
            self.callbacks.push(callback);
        }
    };
}

window.APForumApp = new AlloprofForumApp();
