@extends('layouts.app')

@section('title','Visualisasi Data')

@section('page-title')
<i class="fas fa-chart-line me-2" style="color:#3b82f6;"></i>
Visualisasi Data
@endsection

@push('styles')
<style>

.chart-card{
    background:rgba(15,23,42,.82);
    border:1px solid rgba(59,130,246,.15);
    border-radius:16px;
    padding:20px;
    backdrop-filter:blur(10px);
    box-shadow:0 8px 25px rgba(0,0,0,.15);
}

.chart-title{
    color:#e2e8f0;
    font-size:18px;
    font-weight:700;
    margin-bottom:15px;
}

.filter-card{
    background:rgba(15,23,42,.82);
    border-radius:16px;
    padding:20px;
    margin-bottom:20px;
    border:1px solid rgba(59,130,246,.15);
}

canvas{
    width:100% !important;
    height:300px !important;
}

</style>
@endpush

@section('content')

<div class="container-fluid">

<div class="filter-card">

<div class="row">

<div class="col-md-6">

<label class="form-label text-light">
Negara
</label>

<select class="form-select" id="country">

@foreach($negaraList as $negara)

<option value="{{ $negara->id }}">
{{ $negara->nama }}
</option>

@endforeach

</select>

</div>

<div class="col-md-6">

<label class="form-label text-light">

Jenis Grafik

</label>

<select class="form-select">

<option>Semua Data</option>

<option>GDP</option>

<option>Inflasi</option>

<option>Currency</option>

<option>Risk</option>

</select>

</div>

</div>

</div>


<div class="row g-4">

<div class="col-lg-6">

<div class="chart-card">

<div class="chart-title">

📈 GDP Trend

</div>

<canvas id="gdpChart"></canvas>

</div>

</div>


<div class="col-lg-6">

<div class="chart-card">

<div class="chart-title">

📊 Inflation Trend

</div>

<canvas id="inflationChart"></canvas>

</div>

</div>


<div class="col-lg-6">

<div class="chart-card">

<div class="chart-title">

💱 Currency Trend

</div>

<canvas id="currencyChart"></canvas>

</div>

</div>


<div class="col-lg-6">

<div class="chart-card">

<div class="chart-title">

⚠ Risk Trend

</div>

<canvas id="riskChart"></canvas>

</div>

</div>

</div>

</div>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

// ===================== GDP ======================

const gdpChart = new Chart(
document.getElementById('gdpChart'),
{
    type:'line',

    data:{

        labels:@json($years),

        datasets:[{

            label:'GDP (Trillion USD)',

            data:@json($gdp),

            borderWidth:3,

            tension:.35,

            fill:true

        }]

    },

    options:{
        responsive:true,
        maintainAspectRatio:false
    }

});


// ================= Inflation ====================

const inflationChart = new Chart(
document.getElementById('inflationChart'),
{
    type:'bar',

    data:{

        labels:@json($years),
        datasets:[{

            label:'Inflation',

            data:@json($inflasi),

            borderWidth:2

        }]

    },

    options:{
        responsive:true,
        maintainAspectRatio:false
    }

});



// ================= Currency =====================

const currencyChart = new Chart(
document.getElementById('currencyChart'),
{

type:'line',

data:{

labels:['Jan','Feb','Mar','Apr','Mei','Jun'],

datasets:[{

label:'USD Exchange',

data:[15000,15200,14900,15100,15350,15400],

borderWidth:3,

tension:.4

}]

},

options:{
responsive:true,
maintainAspectRatio:false
}

});



// ================= Risk =====================

const riskChart = new Chart(
document.getElementById('riskChart'),
{

type:'radar',

data:{

labels:[

'Weather',

'Inflation',

'Currency',

'Political',

'Logistic'

],

datasets:[{

label:'Risk Score',

data:[30,45,60,40,25],

borderWidth:2,

fill:true

}]

},

options:{
responsive:true,
maintainAspectRatio:false
}

});

</script>

@endpush