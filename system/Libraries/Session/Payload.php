<?php

namespace System\Libraries\Session;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Config;
use System\Facades\Cookie;
use System\Libraries\Session\Drivers\Driver;
use System\Libraries\Session\Drivers\Sweeper;
use System\Support\Str;

class Payload
{
    public $session;
    public $driver;
    public $exists = true;

    /**
     * Buat instance payload baru.
     *
     * @param \System\Libraries\Session\Drivers\Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Muat session untuk request saat ini.
     *
     * @param  string $id
     *
     * @return void
     */
    public function load($id)
    {
        if (!is_null($id)) {
            $this->session = $this->driver->load($id);
        }

        if (is_null($this->session) || static::expired($this->session)) {
            $this->exists = false;
            $this->session = $this->driver->fresh();
        }

        if (!$this->has('csrf_token')) {
            $this->put('csrf_token', Str::random(40));
        }
    }

    /**
     * Cek apakah payload session masih berlaku atau sudah kadaluwarsa.
     *
     * @param  string $session
     *
     * @return bool
     */
    protected static function expired($session)
    {
        $lifetime = Config::get('session.lifetime');
        
        return (time() - $session['last_activity']) > ($lifetime * 60);
    }

    /**
     * Cek apakah item session / flash yang diberikan ada dalam daftar session.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return (!is_null($this->get($key)));
    }

    /**
     * Ambil item dari session / flash.
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $session = $this->session['data'];

        if (!is_null($value = array_get($session, $key))) {
            return $value;
        } elseif (!is_null($value = array_get($session[':new:'], $key))) {
            return $value;
        } elseif (!is_null($value = array_get($session[':old:'], $key))) {
            return $value;
        }

        return value($default);
    }

    /**
     * Taruh / set sebuah item ke session.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function put($key, $value)
    {
        array_set($this->session['data'], $key, $value);
    }

    /**
     * Taruh / set sebuah item ke flash data.
     * Flash data ini hanya akan tersedia di request saat ini dan request berikutnya.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function flash($key, $value)
    {
        array_set($this->session['data'][':new:'], $key, $value);
    }

    /**
     * Pertahankan seluruh item flash data
     * agar tidak kadaluwarsa diakhir request.
     *
     * @return void
     */
    public function reflash()
    {
        $old = $this->session['data'][':old:'];
        $this->session['data'][':new:'] = array_merge($this->session['data'][':new:'], $old);
    }

    /**
     * Pertahankan satu atau beberapa item flash data
     * agar tidak kadaluwarsa diakhir request.
     *
     * @return void
     */
    public function keep($keys)
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            $this->flash($key, $this->get($key));
        }
    }

    /**
     * Hapus sebuah item dari session.
     *
     * @param  string $key
     *
     * @return void
     */
    public function forget($key)
    {
        array_forget($this->session['data'], $key);
    }

    /**
     * Hapus seluruh item dari session.
     *
     * @return void
     */
    public function flush()
    {
        $this->session['data'] = [
            'csrf_token' => $this->token(),
            ':new:' => [],
            ':old:' => []
        ];
    }

    /**
     * Perbarui session id.
     *
     * @return void
     */
    public function regenerate()
    {
        $this->session['id'] = $this->driver->id();
        $this->exists = false;
    }

    /**
     * Ambil token CSRF.
     *
     * @return string
     */
    public function token()
    {
        return $this->get('csrf_token');
    }

    /**
     * Ambil aktifitas terakhir session.
     *
     * @return int
     */
    public function activity()
    {
        return $this->session['last_activity'];
    }

    /**
     * Simpan payload session ke storage internal.
     * Method ini otomatis dipanggil di akhir requset.
     *
     * @return void
     */
    public function save()
    {
        $this->session['last_activity'] = time();
        $this->age();

        $config = Config::get('session');
        $this->driver->save($this->session, $config, $this->exists);
        $this->cookie($config);

        $sweepage = $config['sweepage'];

        if (mt_rand(1, $sweepage[1]) <= $sweepage[0]) {
            $this->sweep();
        }
    }

    /**
     * Lihat detail data session.
     *
     * @return array
     */
    public function all()
    {
        return $this->session;
    }

    /**
     * Bersihkan session yang telah kadaluwarsa.
     * Jika driver sesionnya adalah Sweeper, ia harus membersihkan session
     * yang sudah kadaluwarsa dari waktu ke waktu.
     * Metode ini men-trigger garbage collector.
     *
     * @return void
     */
    public function sweep()
    {
        if ($this->driver instanceof Sweeper) {
            $this->driver->sweep(time() - (Config::get('session.lifetime') * 60));
        }
    }

    /**
     * Umur session flash data
     *
     * @return void
     */
    protected function age()
    {
        $this->session['data'][':old:'] = $this->session['data'][':new:'];
        $this->session['data'][':new:'] = [];
    }

    /**
     * Kirim Cookie session ID ke browser.
     *
     * @param  array $config
     *
     * @return void
     */
    protected function cookie($config)
    {
        extract($config, EXTR_SKIP);
        $minutes = (!$expire_on_close) ? $lifetime : 0;
        Cookie::put($cookie, $this->session['id'], $minutes, $path, $domain, $secure);
    }
}
