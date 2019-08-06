<?php

return [

    /*
     * The model which handles the plans tables.
     */

    'models' => [

        'plan' => \Iprop\Plans\Models\PlanModel::class,
        'subscription' => \Iprop\Plans\Models\PlanSubscriptionModel::class,
        'feature' => \Iprop\Plans\Models\PlanFeatureModel::class,
        'usage' => \Iprop\Plans\Models\PlanSubscriptionUsageModel::class,

        'stripeCustomer' => \Iprop\Plans\Models\StripeCustomerModel::class,

    ],

];
