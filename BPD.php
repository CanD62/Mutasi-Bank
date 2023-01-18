<?php

date_default_timezone_set("Asia/Jakarta");

class IbParser
{




    function __construct()
    {
        $this->conf['ip']       = file_get_contents( 'https://icanhazip.com/' );
        $this->conf['time']     = time();
        $this->conf['path']     = dirname( __FILE__ );
    }




    function instantiate( $bank )
    {
        $class = $bank . 'Parser';
        $this->bank = new $class( $this->conf ) or trigger_error( 'Undefined parser: ' . $class, E_USER_ERROR );
    }

    function getBalance( $bank, $username, $password )
    {

        $this->instantiate( $bank );
        $token = $this->bank->login( $username, $password );
        $balance = $this->bank->getBalance();
        var_dump($token);
        $this->bank->logout($token);
        return $balance;

    }

    function getTransactions( $bank, $username, $password, $tgl1, $tgl2 )
    {

        $this->instantiate( $bank );
        $token = $this->bank->login( $username, $password );
        if($token == false){
        return 'gagal login';
        } else{
        $transactions = $this->bank->getTransactions($tgl1, $tgl2);
        $this->bank->logout($token);
        return $transactions;
        }

    }

}




class BPDParser
{


    function __construct( $conf )
    {

        $this->conf = $conf;

        $d          = explode( '|', date( 'Y|m|d|H|i|s', $this->conf['time'] ) );
        $start      = mktime( $d[3], $d[4], $d[5], $d[1], ( $d[2] - 3 ), $d[0] );

        $this->post_time['end']['y'] = $d[0];
        $this->post_time['end']['m'] = $d[1];
        $this->post_time['end']['d'] = $d[2];
        $this->post_time['start']['y'] = date( 'Y', $start );
        $this->post_time['start']['m'] = date( 'm', $start );
        $this->post_time['start']['d'] = date( 'd', $start );
    }




    function curlexec()
    {
        curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        return curl_exec( $this->ch );
    }




    function login( $username, $password )
    {

        $this->ch = curl_init();

        curl_setopt( $this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 6.0.1; SM-G532G Build/MMB29T; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 Mobile Safari/537.36' );
        curl_setopt( $this->ch, CURLOPT_URL, 'https://ibanking.bankjateng.co.id/signin' );
        curl_setopt( $this->ch, CURLOPT_COOKIEFILE, $this->conf['path'] . '/cookie' );
        curl_setopt( $this->ch, CURLOPT_COOKIEJAR, $this->conf['path'] . '/cookiejar' );

       $req= $this->curlexec();
        preg_match('/name="signCode" type="hidden" value="(.*?)"/', $req, $signCode);
        $signCode = $signCode[1];
        preg_match('/name="code" type="hidden" value="(.*?)"/', $req, $code);
        $code = $code[1];
        preg_match('/name="_csrf" value="(.*?)"/', $req, $csrf);
        $csrf = $csrf[1];
        $config_password = urlencode('557713;'.$username.'-'.$password.':wHs');
        $params = '_csrf='.$csrf.'&signCode='.$signCode.'&code='.$code.'&password='.$config_password.'&username='.$username.'&password0='.$password.'&submit=Login';
        
        curl_setopt( $this->ch, CURLOPT_URL, 'https://ibanking.bankjateng.co.id/authenticate' );
        curl_setopt( $this->ch, CURLOPT_REFERER, 'https://ibanking.bankjateng.co.id/signin' );
        curl_setopt( $this->ch, CURLOPT_COOKIEFILE, $this->conf['path'] . '/cookie' );
        curl_setopt( $this->ch, CURLOPT_COOKIEJAR, $this->conf['path'] . '/cookiejar' );
        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $params );
        curl_setopt( $this->ch, CURLOPT_POST, 1 );
        curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);


        $p = $this->curlexec();
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $header = substr($p, 0, $header_size);
        preg_match_all('%Set-Cookie: XSRF-TOKEN=(.*?);%',$header,$d);
        preg_match_all('%set-cookie: XSRF-TOKEN=(.*?);%',$header,$e);
        $parse = explode( '<div class="pull-left image" id="prof-pict">', $p );
        if ( empty( $parse[1] ) ){
        return false;
        } else{
        $token = isset($d[1][1]) ? $d[1][1] : $e[1][1];
        return $token;
    }
    }




    function logout($token)
    {
        $params = '_csrf='.$token;
        curl_setopt( $this->ch, CURLOPT_URL, 'https://ibanking.bankjateng.co.id/logout' );
        curl_setopt( $this->ch, CURLOPT_REFERER, 'https://ibanking.bankjateng.co.id/account_history_' );
        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $params );
        curl_setopt( $this->ch, CURLOPT_POST, 1 );
        curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true);
        $this->curlexec();
        return curl_close( $this->ch );
    }




    

    function getTransactions($tgl1,$tgl2)
    {

        curl_setopt( $this->ch, CURLOPT_URL, 'https://ibanking.bankjateng.co.id/account_history/=%3FUTF-8%3FB%3FNjAzMzA0NjMzNQ==%3F=' );
        curl_setopt( $this->ch, CURLOPT_REFERER, 'https://ibanking.bankjateng.co.id/history_acc' );

        $req = $this->curlexec();
        preg_match('/name="_csrf" value="(.*?)"/', $req, $csrf);
        $csrf = $csrf[1];
        $tgl = urlencode($tgl1.' - '.$tgl2);
        $params ='_csrf='.$csrf.'&flagHistory=2&dateFromTo='.$tgl;
        curl_setopt( $this->ch, CURLOPT_URL, 'https://ibanking.bankjateng.co.id/account_history_' );
        curl_setopt( $this->ch, CURLOPT_REFERER, 'https://ibanking.bankjateng.co.id/account_history/=%3FUTF-8%3FB%3FNjAzMzA0NjMzNQ==%3F=' );
        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $params );
        curl_setopt( $this->ch, CURLOPT_POST, 1 );
        curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true);
        $src = $this->curlexec();
        $parse = explode( '<table id="example1" class="table table-bordered table-hover table-striped">', $src );
        if ( empty( $parse[1] ) )
        return false;
        $parse = explode( '</tbody>', $parse[1] );
        $parse = explode( '<tbody>', $parse[0] );
        $parse = explode( '<tr>', $parse[1] );
        unset($parse[0]);
        $cektrx = str_ireplace('<td colspan="7">', '<td>', $parse[1]);

        preg_match('#<td>(.*?)</td>#', $cektrx, $cektrx);
        if($cektrx[1] == 'Tidak ada Data'){
        return 'Tidak ada Data';
        } else{


        $print = array();
        foreach ($parse as $key => $value) {
            $td = trim($parse[$key]);
        $td = str_ireplace('<td>', '#~#~#<td>', $td);
        $td = str_ireplace('<td align="right"><span>', '#~#~#<td>', $td);
        $td = str_ireplace('</span></td>', '</td>', $td);
        $td =  explode('#~#~#', $td);

        preg_match('#<td>(.*?)</td>#', isset($td[2]) ? $td[2] : null, $tglmutasi);
        preg_match('#<td>(.*?)</td>#', isset($td[4]) ? $td[4] : null, $keterangan);
        preg_match('#<td>(.*?)</td>#', isset($td[5]) ? $td[5] : null, $tipe);
        preg_match('#<td>(.*?)</td>#', isset($td[6]) ? $td[6] : null, $jumlah);

        $keterangan = isset($keterangan[1]) ? $keterangan[1] : null;
        $tglmutasi = isset($tglmutasi[1]) ? $tglmutasi[1] : null;
        $tipe = isset($tipe[1]) ? $tipe[1] : null;
        $jumlah = str_replace('-', '', (str_replace('.', '', (trim(isset($jumlah[1]) ? $jumlah[1] : null)))));
        $ar = array($tglmutasi,$keterangan,$tipe,$jumlah);

        array_push($print, $ar);
        }
        }
       
        return $print;

    }

} 