(function($) {
    $(document).ready(function() {
        $('.jsTree').each(function($index, $item) {
            var treeDiv = $($item);

            treeDiv.find('li:not(:has(li))').attr('rel', 'link');
            treeDiv.jstree({
                "plugins": ["themes", "html_data", "types"],
                "themes": {
                    "theme": "classic"
                },
                "core": {
                    "animation": 200
                },
                "types": {
                    "types": {
                        "link": {
                            "icon": {
                                "image": assetsUrl + "/js/jstree/themes/classic/file.png"
                            },
                            "valid_children" : "none"
                        }
                    }
                }
            });//.jstree('open_all');
            treeDiv.jstree("open_node", treeDiv.find('li.selected'));

            treeDiv.find('a[href="#"]').click(function() {
                treeDiv.jstree("toggle_node", $(this).closest('li'));
                return false;
            });
        });
    });
})(jQuery);