define([
    'jquery',
    'uiComponent',
    'moment',
    'mageUtils',
    'text!Amasty_RecurringPayments/template/product/recurring-payments/price.html',
    'mage/template',
    'rjsResolver',
    'mage/translate',
    'tooltip'
], function ($, Component, moment, utils, priceTmpl, template, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_RecurringPayments/product/recurring-payments/table',
            selectComponent: '${ $.parentName }.select',
            dateComponentName: '${ $.parentName }.date',
            StartDateWithTrial: '',
            isAllowSpecifyStartEndDate: '',
            checkedValue: '',
            reloadPrice: '',
            dateFormat: '',
            links: {
                checkedValue: '${ $.selectComponent }:checkedValue',
                reloadPrice: '${ $.selectComponent }:reloadPrice',
                data: '${ $.selectComponent }:data',
                dateFormat: '${ $.dateComponentName }:dateFormat',
                momentFormat: '${ $.dateComponentName }:momentFormat'
            },
            imports: {
                data: '${ $.provider }:plans',
                isFreeShipping: '${ $.provider }:isFreeShipping',
                isAllowSpecifyStartEndDate: '${ $.provider }:isAllowSpecifyStartEndDate',
                originalPrice: '${ $.provider }:originalPrice'
            },
            listens: {
                startDate: 'setStartDate',
                trialDays: 'setStartDate'
            }
        },
        tooltipContentClass: 'amrec-tooltip-content',
        selectors: {
            tooltipBase: '[data-amrec-js="tooltip-base"]',
            tooltipContent: '[data-amrec-js="tooltip-content"]',
            priceHolderPrice: '.product-add-form [data-role="priceBox"], .product-info-price [data-role="priceBox"]',
            bundleSlide: '#bundle-slide'
        },
        translation: {
            amDiscountTooltip: $.mage.__('After the first %1  cycle(s) you will be paying %2 per delivery'),
            // eslint-disable-next-line max-len
            amTrialMessage: $.mage.__('Regular subscription fee (do not confuse it with initial or one-time fee which is paid at checkout) will be charged for the first time after trial period ends. In your case, this will happen in %1 days, %2')
        },

        /**
         * Initializes Table component.
         *
         * @returns {Table} - Chainable.
         */
        initialize: function () {
            this.initSelectors();
            this._super();
            this.initTable();
            this.setStartDate();

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
                    'reloadPrice',
                    'startDate',
                    'StartDateWithTrial',
                    'enableTrial',
                    'trialDays',
                    'isEnableFee',
                    'feeType',
                    'feeAmount',
                    'feeAmountValue',
                    'isEnableDiscount',
                    'discountAmountType',
                    'discountAmount',
                    'discountAmountValue',
                    'isEnableDiscountCycle',
                    'discountCycles',
                    'billingCycleName',
                    'billingCycleUnit',
                    'billingCycleFrequency'
                ]);

            this.billingCycleUnit.extend({ notify: 'always' });
            this.reloadPrice.extend({ notify: 'always' });
            this.checkedValue.subscribe(function (currentNamePlan) {
                this.initTable(currentNamePlan);
            }, this);
            this.reloadPrice.subscribe(this.reloadPriceAction, this);
            $(document).on('changePriceTooltip.amrec', function (event, price) {
                this.originalPrice = price;
            }.bind(this));

            if (this.bundleSlide.length) {
                this.bundleSlide.on('click.amrec', function () {
                    this.reloadPrice(true);
                }.bind(this));
            }

            return this;
        },

        /**
         * @returns {void}
         */
        initSelectors: function () {
            this.priceHolderPrice = $(this.selectors.priceHolderPrice);
            this.tooltipBase = $(this.selectors.priceHolderPrice);
            this.bundleSlide = $(this.selectors.bundleSlide);
        },

        /**
         * @param {String} name
         * @returns {void}
         */
        initTable: function (name) {
            var namePlan = name || this.checkedValue(),
                config = this.getConfigPlan(namePlan),
                trialDays;

            this.enableTrial(config.is_enable_trial);
            trialDays = this.enableTrial() ? config.trial_days : 0;
            this.trialDays(trialDays);
            this.billingCycleName(config.plan_name);
            this.billingCycleFrequency(config.frequency);
            this.isEnableFee(config.is_enable_fee);
            this.feeType(config.fee_type);
            this.feeAmount(config.fee_amount);
            this.feeAmountValue(config.fee_amount_formatted);
            this.isEnableDiscount(config.discount_enabled);
            this.discountAmountType(config.discount_amount_type);
            this.discountAmount(config.discount_amount);
            this.discountAmountValue(config.discount_amount_formatted);
            this.isEnableDiscountCycle(config.discount_cycles_limit_enabled);
            this.discountCycles(config.number_discount_cycles);
            this.billingCycleUnit(config.frequency_unit);
            this.reloadPrice(true);
        },

        /**
         * @return {void}
         */
        reloadPriceAction: function () {
            resolver(function () {
                this.priceHolderPrice.trigger('reloadPrice');
            }, this);
        },

        /**
         * @param {Sting} id
         * @return {Object|void}
         */
        getConfigPlan: function (id) {
            var config;

            if (!this.data) {
                this.data = this.source.get('plans');
            }

            config = this.data;

            if (id) {
                config = config.filter(function (value) {
                    return value.plan_id === id;
                });
            }

            return config[0];
        },

        /**
         * @return {void}
         */
        setStartDate: function () {
            var StartDate = this.startDate(),
                momentFormat = this.momentFormat,
                StartDateWithTrial = StartDate ? moment(StartDate, momentFormat) : moment();

            StartDateWithTrial = StartDateWithTrial.add(this.trialDays(), 'days');
            StartDateWithTrial = StartDateWithTrial.format(momentFormat);

            this.StartDateWithTrial(StartDateWithTrial);
        },

        /**
         * @param {Element} element
         * @returns {void}
         */
        initCyclesTooltip: function (element) {
            $(element).tooltip({
                position: {
                    my: 'left bottom',
                    at: 'left top'
                },
                items: this.selectors.tooltipContent,
                tooltipClass: this.tooltipContentClass,
                content: function () {
                    return this.getTranslateText('amDiscountTooltip');
                }.bind(this)
            });
        },

        /**
         * Get translate text
         *
         * @param {String} name - translate key
         * @returns {String}
         */
        getTranslateText: function (name) {
            var originalPrice = template(priceTmpl, { price: this.originalPrice }),
                translateResult = {
                    amDiscountTooltip: this.translation.amDiscountTooltip
                        .replace('%1', this.discountCycles())
                        .replace('%2', originalPrice),
                    amTrialMessage: this.translation.amTrialMessage
                        .replace('%1', this.trialDays())
                        .replace('%2', this.StartDateWithTrial())
                };

            return translateResult[name];
        }
    });
});
