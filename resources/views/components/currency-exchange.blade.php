@props(['job', 'currencies', 'exchange'])

<div x-data="{
currencyTo: 'EUR',
salary: {{$job->salary}},
currencyObject: {},
loading: false,

async getRates(){
    this.loading = true;
    let response = await fetch(`https://api.frankfurter.dev/v1/latest?amount=${this.salary}&from=NOK&to=${this.currencyTo}`);
    this.currencyObject = await response.json();
    this.loading = false;
}

}" x-init="getRates()">
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Salary</span>
            <i class="fa-solid fa-money-bill-wave text-blue-500"></i>
        </div>

        <div class="bg-white rounded-md p-3 mb-2 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-lg font-bold text-gray-800">{{number_format(round($job->salary))}}</span>
                <span class="text-sm font-semibold text-blue-600 bg-blue-100 px-2 py-1 rounded">NOK</span>
            </div>
        </div>

        <div class="flex justify-center my-2">
            <i class="fa-solid fa-arrow-down text-gray-400"></i>
        </div>

        <select
            @change="getRates()"
            x-model="currencyTo"
            class="w-full px-4 py-2 bg-white border-2 border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer font-semibold text-gray-700 mb-2 transition-all hover:border-blue-400">
            @foreach($currencies as $key => $currency)
                <option value="{{$currency}}">{{$key}}</option>
            @endforeach
        </select>

        <div class="bg-white rounded-md p-3 shadow-sm border-2 border-green-200">
            <div x-show="loading" class="flex items-center justify-center py-2">
                <i class="fa-solid fa-spinner fa-spin text-blue-500 mr-2"></i>
                <span class="text-sm text-gray-600">Converting...</span>
            </div>

            <div x-show="!loading" class="flex items-center justify-between">
                <span class="text-xl font-bold text-gray-900"
                      x-text="Math.round(currencyObject.rates[currencyTo])">
                </span>
                <span class="text-sm font-semibold text-green-600 bg-green-100 px-2 py-1 rounded"
                      x-text="currencyTo"></span>
            </div>
        </div>

        <div x-show="!loading && currencyObject.rates?.[currencyTo]" class="mt-2 text-xs text-gray-500 text-center">
            <i class="fa-solid fa-info-circle mr-1"></i>
            <span>Exchange rate as of </span>
            <span x-text="currencyObject.date"></span>
        </div>
    </div>
</div>
