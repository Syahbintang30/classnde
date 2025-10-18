@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="profile-container">
        <h2 style="margin:0 0 18px 0">Edit Profile</h2>

        <style>
            /* Layout container tweaks */
            /* Prevent horizontal overflow and make page adapt to small viewports */
            html,body{min-width:0;overflow-x:hidden}
            *,*::before,*::after{box-sizing:border-box}
            .profile-container{max-width:900px;margin:40px auto;padding:20px 18px;color:#fff;width:100%}
            @media (max-width:640px){ .profile-container{margin:18px auto;padding:12px 12px} }

            /* Responsive avatar sizes and smooth preview transition */
            #photo-wrap{width:140px;height:140px}
            #photo-preview{transition:opacity .18s ease, transform .18s ease;display:block;width:100%;height:100%;object-fit:cover}
            @media (max-width:900px){ #photo-wrap{width:120px;height:120px} }
            @media (max-width:600px){ #photo-wrap{width:96px;height:96px} }

            /* Modal overlay and dialog transitions */
            #cropper-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:2000;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:opacity .22s ease}
            #cropper-modal.open{opacity:1;pointer-events:auto}
            #cropper-dialog{transform:translateY(6px) scale(.98);transition:transform .22s cubic-bezier(.2,.9,.2,1),opacity .18s ease;opacity:.98;max-height:calc(100vh - 80px);overflow:auto;box-sizing:border-box}
            #cropper-modal.open #cropper-dialog{transform:translateY(0) scale(1);opacity:1}
            /* Slight backdrop blur for elegance (opt-in if supported) */
            #cropper-modal .blur-backdrop{backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}

            /* Crop area should stay square and shrink on small viewports */
            #crop-area{width:min(420px,86vw);aspect-ratio:1/1;height:auto;background:#111;overflow:hidden;position:relative;border-radius:8px;border:1px solid rgba(255,255,255,0.04);touch-action:none}
            #crop-area img{position:absolute;left:0;top:0;will-change:transform;user-select:none;-webkit-user-drag:none}

            /* Zoom control responsive sizing */
            #crop-zoom{width:220px;max-width:60vw}

            /* Forms and sections
               Keep centered but flexible; reduce gaps on small screens */

            /* Sections should not overflow; use full available width on small screens */
            .profile-section{width:100%;max-width:720px;margin:0 auto;background:linear-gradient(180deg,#070707,#0c0c0c);padding:22px;border-radius:12px;border:1px solid rgba(255,255,255,0.03);box-sizing:border-box}
            @media (max-width:640px){ .profile-section{padding:14px;margin:0 6px;border-radius:10px} }

            .profile-form{max-width:520px;margin:0 auto;width:100%}
            @media (max-width:520px){ .profile-form{padding:0 6px;width:100%} }

            /* Buttons flow better on small screens */
            .actions-row{display:flex;gap:10px;align-items:center;margin-top:6px}
            @media (max-width:420px){ .actions-row{flex-direction:column;align-items:stretch} }

            /* Ensure buttons and other controls don't create horizontal overflow */
            button,input,select,textarea{max-width:100%}
            img{max-width:100%;height:auto;display:block}
        </style>

        {{-- Flash/messages --}}
        @if(session('status') || session('success') || session('error') || $errors->any())
            <div style="margin-bottom:18px">
                @if(session('status') === 'profile-updated' || session('success'))
                    <div style="background:linear-gradient(90deg,#0b7a44,#11998e);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(12,120,68,0.18)">
                        {{ session('success') ?? 'Profile updated.' }}
                    </div>
                @endif
                @if(session('status') === 'password-updated')
                    <div style="background:linear-gradient(90deg,#0b7a44,#11998e);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(12,120,68,0.18)">
                        Password updated.
                    </div>
                @endif
                @if(session('error'))
                    <div style="background:linear-gradient(90deg,#c0392b,#e74c3c);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(224,67,67,0.12)">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div style="background:linear-gradient(90deg,#c0392b,#e74c3c);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(224,67,67,0.12)">
                        <ul style="margin:0;padding-left:18px">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

    <div style="display:flex;flex-direction:column;align-items:center;gap:22px">
            {{-- Profile photo preview (moved to top center) --}}
            <div style="text-align:center;margin-bottom:6px">
                @php $avatar = $user->photoUrl(); @endphp
                <div id="photo-wrap" style="margin:0 auto;border-radius:999px;overflow:hidden;border:3px solid rgba(255,255,255,0.06);background:#0b0b0b;display:flex;align-items:center;justify-content:center">
                    @if($avatar)
                        <img id="photo-preview" src="{{ $avatar }}" alt="avatar">
                    @else
                        <svg id="photo-preview" width="96" height="96" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" fill="#ffffff" />
                            <path d="M3 21c0-3.866 3.582-7 9-7s9 3.134 9 7v1H3v-1z" fill="#ffffff" opacity="0.12" />
                        </svg>
                    @endif
                </div>
                <div style="margin-top:12px;">
                    <button id="change-photo" type="button" title="Change profile photo" style="display:inline-flex;align-items:center;gap:10px;background:linear-gradient(180deg,#111,#0f0f0f);border:1px solid rgba(255,255,255,0.06);padding:10px 14px;border-radius:10px;color:#fff;font-weight:700;cursor:pointer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20h9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.5 3.5a2.1 2.1 0 0 1 2.97 2.97L8 18l-4 1 1-4 11.5-11.5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Change Photo
                    </button>
                </div>
            </div>

            {{-- Profile Information --}}
            <section class="profile-section">
                <h3 style="margin:0 0 12px 0">Profile Information</h3>
                <p style="margin:0 0 16px;color:rgba(255,255,255,0.65)">Update your account's profile information and email address.</p>

                <form method="post" action="{{ route('profile.update') }}" class="profile-form" enctype="multipart/form-data">
                    @csrf
                    @method('patch')

                    <div style="margin-bottom:12px">
                        <label for="name" style="display:block;font-weight:700;margin-bottom:6px">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" style="width:100%;padding:10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                        @if($errors->has('name'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('name') }}</div>@endif
                    </div>

                    <div style="margin-bottom:12px">
                        <label for="email" style="display:block;font-weight:700;margin-bottom:6px">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" style="width:100%;padding:10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                        @if($errors->has('email'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('email') }}</div>@endif
                    </div>

                    <div class="actions-row">
                        <button type="submit" style="background:#111;border:1px solid rgba(255,255,255,0.06);padding:10px 14px;border-radius:8px;color:#fff;font-weight:700;cursor:pointer">Save</button>
                        <a href="{{ route('profile') }}" style="color:rgba(255,255,255,0.6);text-decoration:none;padding:8px">Cancel</a>
                    </div>
                    
                    {{-- hidden native file input (kept inside form) --}}
                    <input id="photo" name="photo" type="file" accept="image/*" style="display:none" />
                    @if($errors->has('photo'))<div style="color:#ffb4b4;margin-top:6px;text-align:center">{{ $errors->first('photo') }}</div>@endif

                    {{-- Cropper modal (simple drag + zoom) --}}
                    <div id="cropper-modal">
                        <div id="cropper-dialog" class="blur-backdrop" style="width:100%;max-width:760px;background:linear-gradient(180deg,#0b0b0b,#0f0f0f);border-radius:12px;padding:16px;border:1px solid rgba(255,255,255,0.04);box-shadow:0 24px 80px rgba(0,0,0,0.6);">
                            <div style="display:flex;gap:12px;align-items:flex-start;flex-direction:column">
                                <div style="width:100%;display:flex;justify-content:space-between;align-items:center">
                                    <div style="font-weight:700;color:#fff">Adjust Photo</div>
                                    <div style="display:flex;gap:8px;align-items:center">
                                        <button id="crop-remove" type="button" style="background:transparent;border:1px solid rgba(255,255,255,0.06);padding:8px 10px;border-radius:8px;color:#fff;cursor:pointer">Remove</button>
                                        <button id="crop-cancel" type="button" style="background:transparent;border:1px solid rgba(255,255,255,0.06);padding:8px 10px;border-radius:8px;color:#fff;cursor:pointer">Cancel</button>
                                        <button id="crop-apply" type="button" style="background:linear-gradient(90deg,#ffd166,#ff6b6b);border:none;padding:8px 12px;border-radius:8px;color:#111;font-weight:800;cursor:pointer">Apply</button>
                                    </div>
                                </div>

                                <div style="display:flex;gap:12px;flex-direction:column;align-items:center">
                                    <div id="crop-area">
                                        <img id="crop-image" src="" alt="to crop">
                                        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:80%;height:80%;border:2px dashed rgba(255,255,255,0.18);box-shadow:0 0 0 9999px rgba(0,0,0,0.35) inset;pointer-events:none;border-radius:6px"></div>
                                    </div>
                                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:center">
                                        <label style="color:rgba(255,255,255,0.8);font-weight:700">Zoom</label>
                                        <input id="crop-zoom" type="range" min="0.5" max="3" step="0.01" value="1" style="width:220px">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </section>

            {{-- Password update --}}
            <section id="password" style="width:100%;max-width:720px;margin:0 auto;background:linear-gradient(180deg,#070707,#0c0c0c);padding:22px;border-radius:12px;border:1px solid rgba(255,255,255,0.03)">
                <h3 style="margin:0 0 12px 0">Update Password</h3>
                <p style="margin:0 0 16px;color:rgba(255,255,255,0.65)">Ensure your account is using a long, random password to stay secure.</p>

                <form method="post" action="{{ route('password.update') }}" style="max-width:520px;margin:0 auto;">
                    @csrf
                    @method('put')

                    <div style="margin-bottom:12px">
                        <label for="current_password" style="display:block;font-weight:700;margin-bottom:6px">Current Password</label>
                        <div style="position:relative">
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" style="width:100%;padding:10px 40px 10px 10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                            <button type="button" class="pw-toggle" data-target="current_password" aria-label="Toggle password visibility" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;padding:6px 8px;cursor:pointer;color:rgba(255,255,255,0.85);display:flex;align-items:center;justify-content:center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.4"></circle>
                            </svg>
                            </button>
                        </div>
                        @if($errors->has('current_password'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('current_password') }}</div>@endif
                    </div>

                    <div style="margin-bottom:12px">
                        <label for="password_input" style="display:block;font-weight:700;margin-bottom:6px">New Password</label>
                        <div style="position:relative">
                            <input id="password_input" name="password" type="password" required autocomplete="new-password" style="width:100%;padding:10px 40px 10px 10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                            <button type="button" class="pw-toggle" data-target="password_input" aria-label="Toggle password visibility" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;padding:6px 8px;cursor:pointer;color:rgba(255,255,255,0.85);display:flex;align-items:center;justify-content:center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.4"></circle>
                            </svg>
                            </button>
                        </div>
                        @if($errors->has('password'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('password') }}</div>@endif
                    </div>

                    <div style="margin-bottom:12px">
                        <label for="password_confirmation" style="display:block;font-weight:700;margin-bottom:6px">Confirm Password</label>
                        <div style="position:relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" style="width:100%;padding:10px 40px 10px 10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                            <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Toggle password visibility" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;padding:6px 8px;cursor:pointer;color:rgba(255,255,255,0.85);display:flex;align-items:center;justify-content:center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.4"></circle>
                            </svg>
                            </button>
                        </div>
                        @if($errors->has('password_confirmation'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('password_confirmation') }}</div>@endif
                    </div>

                    <div style="display:flex;gap:10px;align-items:center;margin-top:6px">
                        <button type="submit" style="background:#111;border:1px solid rgba(255,255,255,0.06);padding:10px 14px;border-radius:8px;color:#fff;font-weight:700;cursor:pointer">Save</button>
                        <a href="{{ route('profile') }}" style="color:rgba(255,255,255,0.6);text-decoration:none;padding:8px">Cancel</a>
                    </div>
                </form>
            </section>

            {{-- Delete account removed per request --}}
        </div>
    </div>

    <script>
        // If user clicked "Change Password" link which points to #password, scroll and focus
        (function(){
            if (window.location.hash === '#password') {
                var el = document.getElementById('password');
                if (el) {
                    setTimeout(function(){ el.scrollIntoView({behavior:'smooth', block:'start'}); var input = el.querySelector('input[name="current_password"]'); if (input) input.focus(); }, 80);
                }
            }
        })();
        // password visibility toggles
        (function(){
            var toggles = document.querySelectorAll('.pw-toggle');
            toggles.forEach(function(btn){
                btn.addEventListener('click', function(){
                    var targetId = btn.getAttribute('data-target');
                    var input = document.getElementById(targetId);
                    if (! input) return;
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.style.opacity = '1';
                    } else {
                        input.type = 'password';
                        btn.style.opacity = '0.85';
                    }
                });
            });
        })();
        // Change Photo + Cropper UI (pan + zoom) — produces a cropped blob and sets hidden file input
        (function(){
            var changeBtn = document.getElementById('change-photo');
            var nativeInput = document.getElementById('photo');
            var preview = document.getElementById('photo-preview');
            var modal = document.getElementById('cropper-modal');
            var cropImage = document.getElementById('crop-image');
            var cropArea = document.getElementById('crop-area');
            var zoom = document.getElementById('crop-zoom');
            var apply = document.getElementById('crop-apply');
            var cancel = document.getElementById('crop-cancel');
            var rem = document.getElementById('crop-remove');

            var state = { imgW:0, imgH:0, x:0, y:0, scale:1, isDown:false, startX:0, startY:0 };

            function openFileDialog(){ nativeInput.click(); }
            function showModal(){ modal.style.display = 'flex'; requestAnimationFrame(function(){ modal.classList.add('open'); }); document.body.style.overflow='hidden'; }
            function hideModal(){ modal.classList.remove('open'); setTimeout(function(){ modal.style.display='none'; document.body.style.overflow='auto'; }, 240); }

            changeBtn.addEventListener('click', function(){ openFileDialog(); });

            nativeInput.addEventListener('change', function(){
                var f = this.files && this.files[0];
                if (! f) return;
                var reader = new FileReader();
                reader.onload = function(e){
                    cropImage.src = e.target.result;
                    cropImage.onload = function(){
                        // reset state
                        state.scale = 1; state.x = 0; state.y = 0;
                        state.imgW = cropImage.naturalWidth; state.imgH = cropImage.naturalHeight;
                        updateTransform();
                        zoom.value = 1;
                        showModal();
                    };
                };
                reader.readAsDataURL(f);
                // clear native value if user cancels later
            });

            // pan handling
            cropImage.addEventListener('pointerdown', function(e){ state.isDown = true; state.startX = e.clientX; state.startY = e.clientY; cropImage.setPointerCapture(e.pointerId); });
            window.addEventListener('pointermove', function(e){ if (! state.isDown) return; var dx = e.clientX - state.startX; var dy = e.clientY - state.startY; state.startX = e.clientX; state.startY = e.clientY; state.x += dx; state.y += dy; updateTransform(); });
            window.addEventListener('pointerup', function(){ state.isDown = false; });

            zoom.addEventListener('input', function(){ state.scale = parseFloat(this.value); updateTransform(); });

            function updateTransform(){
                // center by default
                var w = cropArea.clientWidth; var h = cropArea.clientHeight;
                // apply translate + scale
                cropImage.style.transform = 'translate(' + state.x + 'px,' + state.y + 'px) scale(' + state.scale + ')';
                // ensure image covers center box? (light heuristic) - no hard clamping to keep UX simple
            }

            apply.addEventListener('click', function(){
                // draw cropped area to canvas and convert to blob
                var areaRect = cropArea.getBoundingClientRect();
                var deviceRatio = window.devicePixelRatio || 1;
                var outSize = Math.min(800, Math.round(Math.max(300, areaRect.width) * deviceRatio)); // responsive final size
                var canvas = document.createElement('canvas');
                canvas.width = outSize; canvas.height = outSize;
                var ctx = canvas.getContext('2d');
                // compute source region in original image coordinates
                var img = cropImage;
                var areaW = cropArea.clientWidth; var areaH = cropArea.clientHeight;
                var imgRenderedW = img.naturalWidth * state.scale;
                var imgRenderedH = img.naturalHeight * state.scale;
                var offsetX = (areaW/2) - (imgRenderedW/2) + state.x;
                var offsetY = (areaH/2) - (imgRenderedH/2) + state.y;
                var srcX = Math.max(0, Math.round(((-offsetX) ) / state.scale));
                var srcY = Math.max(0, Math.round(((-offsetY) ) / state.scale));
                var srcW = Math.round(areaW / state.scale);
                var srcH = Math.round(areaH / state.scale);
                // clamp
                srcW = Math.min(img.naturalWidth - srcX, srcW);
                srcH = Math.min(img.naturalHeight - srcY, srcH);
                try {
                    ctx.fillStyle = '#0b0b0b'; ctx.fillRect(0,0,outSize,outSize);
                    ctx.drawImage(img, srcX, srcY, srcW, srcH, 0, 0, outSize, outSize);
                } catch (err){
                    alert('Failed to crop image'); hideModal(); return;
                }
                canvas.toBlob(function(blob){
                    if (! blob) { alert('Failed to create image'); hideModal(); return; }
                    // create a File and set to native input via DataTransfer
                    var file = new File([blob], 'profile.jpg', { type: 'image/jpeg' });
                    var dt = new DataTransfer(); dt.items.add(file);
                    nativeInput.files = dt.files;
                    // set preview
                    preview.src = URL.createObjectURL(file);
                    hideModal();
                }, 'image/jpeg', 0.9);
            });

            cancel.addEventListener('click', function(){
                // clear native input if user cancels the flow
                nativeInput.value = '';
                hideModal();
            });

            rem.addEventListener('click', function(){
                if (! confirm('Remove profile photo?')) return;
                // submit remove form
                var f = document.createElement('form'); f.method='POST'; f.action='{{ route('profile.update') }}'; f.style.display='none';
                var token = document.createElement('input'); token.type='hidden'; token.name='_token'; token.value='{{ csrf_token() }}'; f.appendChild(token);
                var method = document.createElement('input'); method.type='hidden'; method.name='_method'; method.value='PATCH'; f.appendChild(method);
                var remi = document.createElement('input'); remi.type='hidden'; remi.name='remove_photo'; remi.value='1'; f.appendChild(remi);
                document.body.appendChild(f); f.submit();
            });

        })();
    </script>
@endsection
