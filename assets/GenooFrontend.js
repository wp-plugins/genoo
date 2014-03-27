
/**
 * Genoo Frontend
 *
 * @version 1.0
 * @author Genoo LLC
 */


/*********************************************************************/

/**
 * Fade effects
 */

(function(){
    var FX = {
        easing: {
            linear: function(progress) {
                return progress;
            },
            quadratic: function(progress) {
                return Math.pow(progress, 2);
            },
            swing: function(progress){
                return 0.5 - Math.cos(progress * Math.PI) / 2;
            },
            circ: function(progress){
                return 1 - Math.sin(Math.acos(progress));
            },
            back: function(progress, x){
                return Math.pow(progress, 2) * ((x + 1) * progress - x);
            },
            bounce: function(progress){
                for (var a = 0, b = 1, result; 1; a += b, b /= 2) {
                    if (progress >= (7 - 4 * a) / 11) {
                        return -Math.pow((11 - 6 * a - 11 * progress) / 4, 2) + Math.pow(b, 2);
                    }
                }
            },
            elastic: function(progress, x){
                return Math.pow(2, 10 * (progress - 1)) * Math.cos(20 * Math.PI * x / 3 * progress);
            }
        },
        animate: function(options){
            var start = new Date;
            var id = setInterval(function() {
                var timePassed = new Date - start;
                var progress = timePassed / options.duration;
                if (progress > 1) {
                    progress = 1;
                }
                options.progress = progress;
                var delta = options.delta(progress);
                options.step(delta);
                if (progress == 1) {
                    clearInterval(id);
                    options.complete();
                }
            }, options.delay || 10);
        },
        fadeOut: function(element, options){
            var to = 1;
            this.animate({
                duration: options.duration,
                delta: function(progress) {
                    progress = this.progress;
                    return FX.easing.swing(progress);
                },
                complete: options.complete,
                step: function(delta) {
                    element.style.opacity = to - delta;
                    element.style.filter = 'alpha(opacity=' + (100 * (to - delta))|0 + ')'
                }
            });
        },
        fadeIn: function(element, options){
            var to = 0;
            element.style.display = 'block'
            this.animate({
                duration: options.duration,
                delta: function(progress) {
                    progress = this.progress;
                    return FX.easing.swing(progress);
                },
                complete: options.complete,
                step: function(delta) {
                    element.style.opacity = to + delta;
                    element.style.filter = 'alpha(opacity=' + (100 * (to + delta))|0 + ')'
                }
            });
        }
    };
    window.FX = FX;
})();


/*********************************************************************/


/**
 * Tools
 * @type {*|Object}
 */

var Tool = Tool || {};


/**
 * Check if element exists
 *
 * @param elem
 * @return {Boolean}
 */

Tool.elementExists = function(elem){ if(elem.length > 0){ return true; } else { return false; } };


/**
 * Remove active class
 *
 * @param from
 */

Tool.removeAllClassOf = function(from, className) {
    var list_items = document.getElementById(from).childNodes;
    for (var i=0, j=list_items.length; i<j; i++){
        var elm = list_items[i];
        if(Tool.hasClass(elm, className)){
            Tool.removeClass(elm, className);
        }
    }
}


/**
 * Switch display state
 *
 * @param element
 */

Tool.switchDisplay = function(element)
{
    if(element.style.display == 'none'){
        element.style.display = '';
    } else {
        element.style.display = 'none';
    }
}


/**
 * Has class
 *
 * @param el
 * @param className
 * @return {Boolean}
 */

Tool.hasClass = function(el, className)
{
    if (el.classList)
        return el.classList.contains(className)
    else
        return new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className)
}


/**
 * Add class
 *
 * @param el
 * @param className
 */

Tool.addClass = function(el, className)
{
    if (el.classList)
        el.classList.add(className);
    else
        el.className += ' ' + className;
}


/**
 * Remove class
 *
 * @param el
 * @param className
 */

Tool.removeClass = function(el, className)
{
    if (el.classList)
        el.classList.remove(className);
    else
        el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
}

/**
 * Switch class
 *
 * @param element
 */

Tool.switchClass = function(element, className)
{
    if(Tool.hasClass(element, className)){
        Tool.removeClass(element, className);
    } else {
        Tool.addClass(element, className);
    }
}


/*********************************************************************/

/**
 * Modal windows
 * @type {*|Object}
 */

var Modal = Modal || {};


/**
 * Display
 *
 * @param e
 * @param modalId
 */

Modal.display = function(e, modalId)
{
    e.preventDefault();
    var doc = document.documentElement, body = document.body;
    var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
    var modal = document.getElementById(modalId);
    if(modalId !== null){
        FX.fadeIn(document.getElementById('genooOverlay'),{
            duration: 400,
            complete: function(){
                Tool.switchClass(document.getElementById('genooOverlay'), 'visible');
                Tool.addClass(modal, 'visible');
            }
        });
    }
}


/**
 * Close
 *
 * @param e
 * @param modalId
 */

Modal.close = function(e, modalId)
{
    e.preventDefault();
    var modalOverlay = document.getElementById('genooOverlay');
    FX.fadeOut(modalOverlay, {
        duration: 400,
        complete: function(){
            Tool.switchClass(document.getElementById('genooOverlay'), 'visible');
            modalOverlay.style.display = 'none';
            Tool.removeAllClassOf('genooOverlay', 'visible');
        }
    });
}