(function() {
    tinymce.create('tinymce.plugins.leaderboard', {
        init : function(ed, url) {
            
            ed.addButton('leaderboard', {
                title : 'Insert Leaderboard',
                image : url + '/leaderboard_sc.png',
                onclick : function() {
                    ed.selection.setContent('[leaderboard]');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        }
    });
    tinymce.create('tinymce.plugins.run_scorer', {
        init : function(ed, url) {
            
            ed.addButton('run_scorer', {
                title : 'Insert Top Run Scorer',
                image : url + '/run_scorer_sc.png',
                onclick : function() {
                    ed.selection.setContent('[run_scorer]');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        }
    });
    tinymce.create('tinymce.plugins.wicket_taker', {
        init : function(ed, url) {
            ed.addButton('wicket_taker', {
                title : 'Insert Leading Wicket Taker',
                image : url + '/wicket_taker_sc.png',
                onclick : function() {
                    ed.selection.setContent('[wicket_taker]');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        }
    });
    tinymce.create('tinymce.plugins.deadline', {
        init : function(ed, url) {
            ed.addButton('deadline', {
                title : 'Insert Deadline Timer',
                image : url + '/deadline_sc.png',
                onclick : function() {
                    ed.selection.setContent('[deadline]');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        }
    });
    tinymce.PluginManager.add('leaderboard', tinymce.plugins.leaderboard);
    tinymce.PluginManager.add('run_scorer', tinymce.plugins.run_scorer);
    tinymce.PluginManager.add('wicket_taker', tinymce.plugins.wicket_taker);
    tinymce.PluginManager.add('deadline', tinymce.plugins.deadline);
})();


