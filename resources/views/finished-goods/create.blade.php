@extends('layouts.app')

@section('title', 'Finished Goods')
@section('page-icon') <i class="fas fa-info-circle"></i> @endsection
@section('page-title', 'Finished Goods Information')
@section('page-description', 'Finished goods inventory is automatically created when transfers are recorded')

@section('content')
<div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg max-w-2xl mx-auto">
    <h3 class="text-lg font-semibold text-blue-900 mb-4">
        <i class="fas fa-lightbulb mr-2"></i> Finished Goods Records
    </h3>
    <p class="text-blue-800 mb-4">
        Finished goods inventory is <strong>automatically created</strong> when production transfers are recorded. 
        You cannot manually create finished goods records.
    </p>
    
    <div class="space-y-3 text-sm text-blue-700">
        <p>
            <strong>How it works:</strong>
        </p>
        <ol class="list-decimal list-inside space-y-2 ml-2">
            <li>Production team records a <strong>Transfer</strong> (production output)</li>
            <li>System automatically creates/updates <strong>Finished Goods</strong> inventory</li>
            <li>Inventory team can then <strong>view and edit</strong> the finished goods records</li>
        </ol>
    </div>

    <div class="mt-6 pt-6 border-t border-blue-200">
        <a href="{{ route('finished-goods.index') }}" class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
            <i class="fas fa-arrow-left mr-2"></i> View Finished Goods
        </a>
    </div>
</div>
@endsection