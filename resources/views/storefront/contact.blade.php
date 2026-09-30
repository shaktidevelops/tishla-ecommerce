@extends('layouts.storefront')

@section('content')
<section class="luxury-page-head contact-head">
  <span class="eyebrow">TISHLA CONCIERGE · SURAT</span>
  <h1>Let's talk fashion.</h1>
  <p>For styling help, product questions, wholesale enquiries or a little help choosing your occasionwear, reach us directly.</p>
</section>

<section class="contact-shell">
  <div class="contact-intro">
    <span class="eyebrow">PERSONAL SERVICE</span>
    <h2>Not sure what to choose?</h2>
    <p>Tell us the occasion, preferred colour, size and budget. The Tishla team can guide you toward pieces that fit the moment.</p>
    <div class="contact-direct">
      <a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer"><span>WhatsApp</span><b>Chat with us ↗</b></a>
      <a href="mailto:{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }}" target="_blank" rel="noopener noreferrer"><span>Email</span><b>{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }} ↗</b></a>
      <a href="{{ route('track-order') }}"><span>Orders</span><b>Track an order ↗</b></a>
    </div>
  </div>

  <div class="contact-form-card">
    <span class="eyebrow">SEND A MESSAGE</span>
    <form method="post" action="{{ route('contact.submit') }}" class="support-form">
      @csrf
      <div class="support-grid-2">
        <label>Name<input name="name" value="{{ old('name') }}" required></label>
        <label>Phone<input name="phone" value="{{ old('phone') }}"></label>
      </div>
      <label>Email<input type="email" name="email" value="{{ old('email') }}"></label>
      <label>Subject<input name="subject" value="{{ old('subject') }}" placeholder="Styling / order / wholesale / other"></label>
      <label>Message<textarea name="message" rows="6" required placeholder="How can we help?"></textarea></label>
      @if($errors->any())<div class="status-banner danger">{{ $errors->first() }}</div>@endif
      <button class="btn btn-dark" type="submit">SEND TO TISHLA <span>↗</span></button>
    </form>
  </div>
</section>
@endsection
