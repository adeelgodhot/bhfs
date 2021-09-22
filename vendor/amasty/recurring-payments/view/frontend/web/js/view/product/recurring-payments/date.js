define([
    'jquery',
    'uiElement',
    'moment',
    'mageUtils',
    'mage/calendar'
], function ($, Component, moment, utils) {
    'use strict';

    /**
     * Object for normalization format Moment.js
     */
    var normalFormatMoment = {
            'day': 'days',
            'month': 'months',
            'week': 'weeks',
            'year': 'years'
        },
        valueEndDateDefault = 'amrec-end-date';

    return Component.extend({
        defaults: {
            template: 'Amasty_RecurringPayments/product/recurring-payments/date',
            tableComponent: 'amasty-recurring-payments.table',
            clientTimezone: null,
            datePlaceholder: 'dd/mm/yyyy',
            trialDays: 0,
            startDate: '',
            endDate: '',
            minEndDate: '',
            billingCycleUnit: '',
            billingCycleFrequency: 0,
            dateFormat: 'dd/MM/yyyy',
            defaultChecked: 'amrec-end-infinite',
            links: {
                enableTrial: '${ $.tableComponent }:enableTrial',
                trialDays: '${ $.tableComponent }:trialDays',
                startDate: '${ $.tableComponent }:startDate',
                billingCycleFrequency: '${ $.tableComponent }:billingCycleFrequency',
                billingCycleUnit: '${ $.tableComponent }:billingCycleUnit'
            },
            imports: {
                isAllowSpecifyStartEndDate: '${ $.provider }:isAllowSpecifyStartEndDate',
                dateFormat: '${ $.provider }:dateFormat'
            },
            listens: {
                billingCycleUnit: 'setEndDate'
            }
        },
        dateConfig: {
            minDate: new Date(),
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            buttonText: '',
            firstDay: 1,
            showOn: 'both',
            showOtherMonths: true,
            beforeShow: function (input, instance) {
                instance.dpDiv.addClass('amrec-calendar-component');
            }
        },
        momentFormatCached: {},
        selectors: {
            subscriptionEndDatepicker: '[data-amrec-js="subscription-end-date"]'
        },

        /**
         * Initializes Table component.
         *
         * @returns {Table} - Chainable.
         */
        initialize: function () {
            this._super();

            this.momentFormat = utils.convertToMomentFormat(this.dateFormat);
            this.dateConfig.dateFormat = this.dateFormat;
            this.dateConfigStartDate = $.extend({}, this.dateConfig);
            this.dateConfigEndDate = $.extend({}, this.dateConfig);

            if (this.isAllowSpecifyStartEndDate) {
                this.setClientTimezone();
                this.setStartDate();
                this.checkedValue(this.defaultChecked);
            }

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Table} - Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'checkedValue',
                    'clientTimezone',
                    'datePlaceholder',
                    'startDate',
                    'endDate',
                    'minEndDate',
                    'enableTrial',
                    'trialDays',
                    'billingCycleFrequency',
                    'billingCycleUnit'
                ]);

            this.billingCycleUnit.extend({ notify: 'always' });
            this.startDate.subscribe(function () {
                this.setEndDate();
            }, this);

            return this;
        },

        /**
         * @return {void}
         */
        setClientTimezone: function () {
            var clientTimezoneOffset = new Date().getTimezoneOffset();

            this.clientTimezone(clientTimezoneOffset * -1);
        },

        /**
         * @returns {void}
         */
        setStartDate: function () {
            var startDate = moment(),
                momentFormat = this.momentFormat;

            startDate = startDate.format(momentFormat);

            this.startDate(startDate);
        },

        /**
         * Set Minimal Subscription End Date by selected Start Date and Billing Cycle
         *
         * @returns {void}
         */
        setEndDate: function () {
            var endDate = this.startDate(),
                endDatepicker = $(this.selectors.subscriptionEndDatepicker),
                momentFormat = this.momentFormat;

            if (!endDate) {
                return;
            }

            endDate = moment(endDate, momentFormat);

            if (this.enableTrial()) {
                endDate = endDate.add(this.trialDays(), 'days');
            }

            if (this.billingCycleFrequency()) {
                endDate = endDate.add(this.billingCycleFrequency(), normalFormatMoment[this.billingCycleUnit()]);
            }

            this.dateConfigEndDate.minDate = endDate.toDate();
            endDatepicker.datepicker('option', 'minDate', endDate.format(momentFormat));
            this.minEndDate(endDate.format(momentFormat));

            if (this.checkedValue() !== valueEndDateDefault) {
                this.endDate(endDate.format(momentFormat));
            }
        },

        /**
         * Is disabled input by radio value
         *
         * @param {String} name
         * @returns {boolean}
         */
        isDisabled: function (name) {
            return name !== this.checkedValue();
        }
    });
});
