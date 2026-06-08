@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="grid cols-2">
            <div class="card">
                <div class="tag">Contact hospital</div>
                <h1>Get in touch</h1>
                <p>Use this page for appointments, department inquiries, and general hospital support.</p>
                <div class="list">
                    <div class="list-item">
                        <strong>Emergency</strong>
                        <p>999 or the hospital emergency desk</p>
                    </div>
                    <div class="list-item">
                        <strong>Reception</strong>
                        <p>+880 1234 567890</p>
                    </div>
                    <div class="list-item">
                        <strong>Email</strong>
                        <p>care@hellomed.test</p>
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>Visit hours</h3>
                <p>Saturday to Thursday: 8:00 AM - 10:00 PM</p>
                <p>Friday: Emergency only</p>
                <p>Offline services can be booked online through doctor profiles and appointment forms.</p>
            </div>
        </div>
    </section>
@endsection
