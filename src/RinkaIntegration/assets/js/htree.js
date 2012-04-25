function initHTree(options, compact) {
    (function($) {
        $(function() {
            var animating = false;
            var uls = $('.horizontalMenu ul:not(.fake)');
            var hoverIn = function(el) {
                el.data('hovered', true);
                if (animating) return;
                var sum = 0;
                el.children().each(function() {sum += $(this).height();});
                el.height(20).stop().animate({'height': sum}, 'fast');
            };
            function hoverOut(el) {
                el.data('hovered', false);
                if (animating) return;
                el.stop().animate({'height': 16}, 'fast');
            };
            if (compact) {
                uls.live('mouseenter mouseleave', function(event) {
                    if (event.type == 'mouseenter') {
                        hoverIn($(this));
                    } else {
                        hoverOut($(this));
                    }
                });
                uls.not(':last').addClass('notLast');
            } else {
                uls.addClass('notLast');
            }
            uls.find('li:not(:has(a))').live('click', function() {
                if (animating) return;
                var li = $(this);
                animating = true;
                li.prevAll().css({'position': 'relative'}).animate({'top': '+' + li.height()});
                var finished1 = false;
                var finished2 = false;

                li.css({'position': 'relative'}).animate({'top': -li.position().top}, function() {
                    li.prependTo(li.parent());
                    li.siblings().andSelf().css({'top': 0});
                    animating = false;
                    if (!li.parent().data('hovered') && compact) {
                        hoverOut(li.parent());
                    }
                    if (finished2) {
                        reloadMenu(li);
                        finished2 = false;
                    } else {
                        finished1 = true;
                    }
                });
                var notNeeded = li.parent().parent().nextAll();
                if (notNeeded.length === 0) {
                    finished2 = true;
                } else {
                    notNeeded.slideUp('normal', function() {
                        $(this).remove();
                        if (finished1) {
                            reloadMenu(li);
                            finished1 = false;
                        } else {
                            finished2 = true;
                        }
                    });
                }
            });
        });
        function reloadMenu(li) {
            var ulDiv = li.parent().parent();
            var zindex = 200;
            var opt = options;
            ulDiv.prevAll().andSelf().children('ul:not(.fake)').each(function() {
                var id = $(this).children().eq(0).attr('data-id');
                opt = opt[id].children;
                zindex--;
            });
            loadSubmenu(opt, ulDiv.parent(), zindex);
        }
        function loadSubmenu(opt, container, zindex) {
            if (typeof opt === 'undefined') return;
            var newUl = $('<ul />');
            for (i in opt) {
                if (parseInt(i) == i) {
                    var newLi = $('<li />').attr('data-id', i);
                    if (typeof opt[i].link !== 'undefined') {
                        newLi.append($('<a />').attr('href', opt[i].link).text(opt[i].title));
                    } else {
                        newLi.text(opt[i].title);
                        newUl.addClass('notLast');
                    }
                    newUl.append(newLi).css('z-index', zindex);
                }
            }
            var div = $('<div />')
                .addClass('ul')
                .append(newUl)
                .append(newUl.clone().addClass('fake'))
                .hide()
                .appendTo(container)
                .slideDown();
            if (compact) {
                for (i in opt) {
                    loadSubmenu(opt[i].children, container, zindex - 1);
                    break;
                }
            } else {
                if (div.prev().is(':visible') && div.offset().top - div.prevAll().eq(0).offset().top > 2) {
                    div.css('clear', 'both');
                }
            }
        }
    })(jQuery);
}