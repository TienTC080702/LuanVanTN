(function() {

    var langs_map = {
        "aff_dep": "Affiliate Queries",
        "ib_dep": "IB Queries"
    };

    var connected = false;

    function zopimInit(){
        $zopim(function() {

            //$zopim.livechat.clearAll();

            if(lang == 'ar') {
                $zopim.livechat.window.setPosition('bl');
            }

            $zopim.livechat.removeTags("undefined");

            $zopim.livechat.set({
                language: lang,
                name: username,
                email: email
            });

            if(lang == 'zh') {
                $zopim.livechat.setLanguage('zh_CN');
            }
            if(lang == 'hk') {
                $zopim.livechat.setLanguage('zh_TW');
            }

            console.log("Chat setLabel");
            $zopim.livechat.departments.setLabel(label);

            console.log("Chat addTags");
            $zopim.livechat.addTags(id, lang, email);

            $zopim.livechat.setOnConnected(function() {

                console.log("Chat connected");

                var aff_deps = $zopim.livechat.departments.getAllDepartments()
                    .filter(function(i) {return i.name == langs_map['aff_dep']})
                    .map(function(i) {return i.name});

                var ib_deps = $zopim.livechat.departments.getAllDepartments()
                    .filter(function(i) {return i.name == langs_map['ib_dep']})
                    .map(function(i) {return i.name});

                var current_deps = aff_deps.concat(ib_deps).filter(function(i) {return i}).map(function(i) {return i});

                console.log("Chat deps:", current_deps);
                $zopim.livechat.departments.filter.apply($zopim.livechat.departments, current_deps);
                connected = true;
                popOut();
            });

            // $zopim.livechat.setOnChatEnd(function(){
            //     $zopim.livechat.clearAll();
            //     console.log("Chat end");
            // });
        });
    }

    function popOut(){
        $zopim.livechat.window.show();
    }
    
    window.onChatOpen  = function() {
        //clicked = true;
        if(connected){
            popOut();
        } else {
            zopimInit();
        }
    };


})(jQuery);

