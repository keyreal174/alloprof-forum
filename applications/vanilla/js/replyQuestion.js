jQuery(document).ready(function($) {
    APForumApp.onReady(function(apForumApp) {
        apForumApp.app.attachListener('userscreen:transition:login_to_signup', function() {
            console.log('show signup')
            apForumApp.obs.trigger('usercreate:show');
        });
        apForumApp.app.attachListener('userscreen:transition:signup_to_login', function() {
            apForumApp.obs.trigger('userlogin:show');
            console.log('show signin')
        });
        apForumApp.app.attachListener('userscreen:transition:login_to_forgotpassword', function() {
            apForumApp.obs.trigger('userforgotpassword:show');
            console.log('show fpass')
        });
        apForumApp.app.attachListener('userscreen:transition:forgotpassword_to_signup', function() {
            apForumApp.obs.trigger('usercreate:show');
            console.log('show signup from fpass')
        });

        apForumApp.app.attachListener('user:loggedin', function(user) {
            console.log('logged in');
            // ssoLogin(auth.currentUser);
        });

      var pathname = window.location.pathname;
      var isEnglish = pathname.indexOf('/helpzone/') > -1;

      if (apForumApp.obs.isAppReady('appa')) {
        apForumApp.obs.trigger('language:change', isEnglish ? 'en': 'fr');
        apForumApp.obs.trigger('language:load', isEnglish ? 'en': 'fr');
      } else {
        var eventID = apForumApp.app.attachListener('OBSERVER:APP:ATTACHED', function (app) {
          if (app == 'appa') {
            apForumApp.obs.trigger('language:change', isEnglish ? 'en': 'fr');
            apForumApp.obs.trigger('language:load', isEnglish ? 'en': 'fr');
            apForumApp.app.detachListenerByUID(eventID);
          }
        });
      }

      apForumApp.obs.trigger('userrappel:show', null);
    });

    const showLogin = function() {
        APForumApp.onReady(function(apForumApp) {
            apForumApp.app.attachListener('userlogin:done', function(userConnected) {
                if (userConnected.actionFinished) {
                    ssoLogin(auth.currentUser);
                }
                console.log('logged in');
            });
            apForumApp.obs.trigger('userlogin:show');
        });
    }

    const showGeoBlockingModal = function() {
        if (!window.APForumApp) {
            window.APForumApp = new AlloprofForumApp();
        }
        // show geoblocking modal

        APForumApp.onReady(function(apForumApp) {
            var pathname = window.location.pathname;
            var isEnglish = pathname.indexOf('/helpzone/') > -1;
            if (apForumApp.obs.isAppReady('appa')) {
                apForumApp.obs.trigger('geoblocking:show', isEnglish ? 'en': 'fr');
                apForumApp.app.attachListener('geoblocking:close', function() {
                    apForumApp.obs.trigger('geoblocking:hide');
                });
                apForumApp.app.attachListener('geoblocking:done', function() {
                    // apForumApp.obs.trigger('userlogin:show');
                    localStorage.setItem('geoBlockingModalTrigger', true);
                    if (auth.currentUser) {
                        var pathname = window.location.pathname;
                        var isEnglish = pathname.indexOf('/helpzone/') > -1;
                        if (isEnglish) {
                            window.location.href = "https://fr.research.net/r/AP-Geo-EN";
                        } else {
                            window.location.href = "https://fr.research.net/r/AP-Geo-FR";
                        }
                    } else {
                        showLogin();
                    }
                });
            }
        })
    }

    $('div.ReplyQuestionButton').click(function() {
        if ($(this).hasClass('teacher')) {
            $('.CommentPostForm').addClass('open');
            $('.information-block.newcomment').addClass('show');
            return;
        }
        if (auth && auth.currentUser) {
            auth.currentUser.getIdToken().then(function(idToken) {  // <------ Check this line
                $.ajax({
                    type: "POST",
                    url: "https://us-central1-alloprof-stg.cloudfunctions.net/apiFunctionsApp/geo/probe",
                    headers: {
                        'authorization': 'Bearer ' + idToken
                    },
                    dataType: 'json',
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log(XMLHttpRequest.responseText);
                    },
                    success: function(json) {
                        const { inZone } = json;
                        localStorage.setItem("inZone", inZone);
                        if (!inZone) {
                            var geoBlockingModalTrigger = localStorage.getItem("geoBlockingModalTrigger");
                                showGeoBlockingModal();
                        } else {
                            $('.CommentPostForm').addClass('open');
                            $('.information-block.newcomment').addClass('show');
                        }
                    }
                });
            });
        } else {
            // $('.CommentPostForm').addClass('open');
            // $('.information-block.newcomment').addClass('show');
        }
    })

    $('.CommentPostForm .close-icon').click(function(){
        $('.CommentPostForm').removeClass('open');
        $('.information-block.newcomment').removeClass('show');
    })
});
