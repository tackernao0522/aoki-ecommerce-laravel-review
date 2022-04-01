## 96 Micromodal.js(画像選択)

シンプル・軽量・VanillaJS<br>
https://micromodal.vercel.app/ <br>

npm instsll micromodal --save <br>

HTML/CSS のサンプル<br>
https://gist.github.com/ghosh/ <br>

### ハンズオン

- `$ npm install micromodal --save`を実行<br>

* `resources/js/bootstrap.js`を編集<br>

```js:bootstrap.js
window._ = require('lodash')

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios')

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// 追加
import MicroModal from 'micromodal' // es6 module

MicroModal.init({
  disableScroll: true,
})
// ここまで

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
```

- `$ touch resources/css/micromodal.css`を実行<br>

* `resources/css/micromodlal.css`を編集<br>

```css:micromodal.css
/**************************\
  Basic Modal Styles
\**************************/

.modal {
  font-family: -apple-system, BlinkMacSystemFont, avenir next, avenir, helvetica
      neue, helvetica, ubuntu, roboto, noto, segoe ui, arial, sans-serif;
}

.modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal__container {
  background-color: #fff;
  padding: 30px;
  max-width: 1200px;
  max-height: 100vh;
  border-radius: 4px;
  overflow-y: auto;
  box-sizing: border-box;
}

.modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal__title {
  margin-top: 0;
  margin-bottom: 0;
  font-weight: 600;
  font-size: 1.25rem;
  line-height: 1.25;
  color: #00449e;
  box-sizing: border-box;
}

.modal__close {
  background: transparent;
  border: 0;
}

.modal__header .modal__close:before {
  content: '\2715';
}

.modal__content {
  margin-top: 2rem;
  margin-bottom: 2rem;
  line-height: 1.5;
  color: rgba(0, 0, 0, 0.8);
}

.modal__btn {
  font-size: 0.875rem;
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  background-color: #e6e6e6;
  color: rgba(0, 0, 0, 0.8);
  border-radius: 0.25rem;
  border-style: none;
  border-width: 0;
  cursor: pointer;
  -webkit-appearance: button;
  text-transform: none;
  overflow: visible;
  line-height: 1.15;
  margin: 0;
  will-change: transform;
  -moz-osx-font-smoothing: grayscale;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  -webkit-transform: translateZ(0);
  transform: translateZ(0);
  transition: -webkit-transform 0.25s ease-out;
  transition: transform 0.25s ease-out;
  transition: transform 0.25s ease-out, -webkit-transform 0.25s ease-out;
}

.modal__btn:focus,
.modal__btn:hover {
  -webkit-transform: scale(1.05);
  transform: scale(1.05);
}

.modal__btn-primary {
  background-color: #00449e;
  color: #fff;
}

/**************************\
  Demo Animation Style
\**************************/
@keyframes mmfadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes mmfadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

@keyframes mmslideIn {
  from {
    transform: translateY(15%);
  }
  to {
    transform: translateY(0);
  }
}

@keyframes mmslideOut {
  from {
    transform: translateY(0);
  }
  to {
    transform: translateY(-10%);
  }
}

.micromodal-slide {
  display: none;
}

.micromodal-slide.is-open {
  display: block;
}

.micromodal-slide[aria-hidden='false'] .modal__overlay {
  animation: mmfadeIn 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide[aria-hidden='false'] .modal__container {
  animation: mmslideIn 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide[aria-hidden='true'] .modal__overlay {
  animation: mmfadeOut 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide[aria-hidden='true'] .modal__container {
  animation: mmslideOut 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.micromodal-slide .modal__container,
.micromodal-slide .modal__overlay {
  will-change: transform;
}
```

- `resources/css/app.css`を編集<br>

```css:app.css
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
/* 追加 */
@import 'micromodal';
```

- `resources/views/owner/products/create.blade.php`を編集<br>

```php:create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            商品登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
                    <form method="post" action="{{ route('owner.products.store') }}">
                        @csrf
                        <div class="-m-2">
                            <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                    <select name="category">
                                        @foreach ($categories as $category)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->secondary as $secondary)
                                                    <option value="{{ $secondary->id }}"
                                                        {{ old('category') == $secondary->id ? 'selected' : '' }}>
                                                        {{ $secondary->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            // 追加
                            <x-select-image name="image1" />

                            <div class="p-2 w-full flex justify-around mt-4">
                                <button type="button" onclick="location.href='{{ route('owner.products.index') }}'"
                                    class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-gray-400 rounded text-lg">戻る</button>
                                <button type="submit"
                                    class="text-white bg-purple-500 border-0 py-2 px-8 focus:outline-none hover:bg-purple-600 rounded text-lg">登録する</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

- `$ touch resources/views/components/select-image.blade.php`を実行<br>

* `resources/views/components/select-image.blade.php`を編集<br>

```php:select-image.blade.php
<div class="modal micromodal-slide" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
            <header class="modal__header">
                <h2 class="modal__title" id="modal-1-title">
                    Micromodal
                </h2>
                <button type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </header>
            <main class="modal__content" id="modal-1-content">
                <p>
                    Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also,
                    <code>esc</code> to close modal.
                </p>
            </main>
            <footer class="modal__footer">
                <button type="button" class="modal__btn modal__btn-primary">Continue</button>
                <button type="button" class="modal__btn" data-micromodal-close
                    aria-label="Close this dialog window">Close</button>
            </footer>
        </div>
    </div>
</div>

<a data-micromodal-trigger="modal-1" href='javascript:;'>Open Modal Dialog</a>
```
