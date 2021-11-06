jQuery(document).ready(function($) {
    firebase.auth().onAuthStateChanged(function(user) {
        if (user) {
            user.getIdToken().then(function(idToken) {  // <------ Check this line

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
                        console.log(json)
                        const { inZone } = json;
                        localStorage.setItem("inZone", inZone);
                        if (!inZone) {
                            showGeoBlockingModal();
                        }
                    }
                });
            });
        } else {
            $.ajax({
                type: "POST",
                url: "https://us-central1-alloprof-stg.cloudfunctions.net/apiFunctionsApp/geo/probe",
                dataType: 'json',
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log(XMLHttpRequest.responseText);
                },
                success: function(json) {
                    console.log(json)
                    const { inZone } = json;
                    localStorage.setItem("inZone", inZone);
                    if (!inZone) {
                        showGeoBlockingModal();
                    }
                }
            });
        }
    });
});
