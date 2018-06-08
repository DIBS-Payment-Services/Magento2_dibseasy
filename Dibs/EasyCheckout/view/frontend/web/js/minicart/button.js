define(
    [
        'uiComponent',
        'jquery',
        'domReady!'
    ],
    function (
        Component,
        $
    ) {
        'use strict';

        return Component.extend({

            defaults: {},

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();

                return this.initEvents();
            },

            /**
             * @returns {Object}
             */
            initEvents: function () {
                $('#' + this.id).off('click.' + this.id)
                    .on('click.' + this.id, this.click.bind(this));

                return this;
            },

            /**
             * @param {Object} event
             * @returns void
             */
            click: function (event) {
                event.preventDefault();

                $('#' + this.dibsEasyCheckoutButton).click();
            }
        });
    }
);
