<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Configuracoes\Usuacgm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'configuracoes.db_usuarios';

    protected $primaryKey = 'id_usuario';

    protected $with = ['usuacgm'];

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
         'nome',
        'email',
        'senha',
        'login',
        'usuarioativo',
        'administrador',
        'admintransparencia'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'senha',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->senha;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->id_usuario;
    }

    /**
     * Get the remember token for the user.
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the remember token for the user.
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Método para verificar a senha usando SHA1
     */
    public function validatePassword($password)
    {
        return sha1($password) === $this->senha;
    }

    /**
     * Buscar usuário por login
     */
    public static function findByLogin($login)
    {
        return static::where('login', $login)->first();
    }

    /**
     * Verificar credenciais do usuário
     */
    public static function validateCredentials($login, $password)
    {
        $user = static::findByLogin($login);
        
        if (!$user) {
            return false;
        }

        return $user->validatePassword($password);
    }

    /**
     * Verificar se o usuário tem permissão para acessar o portal E a instituição
     */
    public function temPermissaoCompleta(): bool
    {
        // Primeiro verifica se tem permissão para o portal
        if (!$this->temPermissaoPortal()) {
            return false;
        }

        // SEMPRE verifica se tem acesso à instituição (obrigatório para todos)
        return $this->temAcessoInstituicao();
    }

    /**
     * Verificar se o usuário tem permissão para acessar o portal (SEM verificação de instituição)
     */
    public function temPermissaoPortal(): bool
    {
        // Usuários inativos não podem acessar o portal
        if ($this->usuarioativo == 0) {
            return false;
        }

        // Usuários com login contendo '.contass' podem acessar
        if (str_contains($this->login, '.contass')) {
            return true;
        }

        // Usuários com campo administrador = 1 podem acessar
        if ($this->administrador == 1) {
            return true;
        }

        // Demais usuários só podem acessar se admintransparencia = 1
        return $this->admintransparencia == 1;
    }

    /**
     * Verificar se o usuário tem acesso à instituição específica
     */
    public function temAcessoInstituicao(): bool
    {
        $userId = $this->id_usuario;
        $codigoInstituicao = CODIGOINSTITUICAO;
        $resultados = DB::table('configuracoes.db_config as c')
            ->leftJoin('configuracoes.db_userinst as u', function($join) use ($userId) {
                $join->on('u.id_instit', '=', 'c.codigo')
                     ->where('u.id_usuario', '=', $userId);
            })
            ->select('c.codigo', 'c.nomeinst', 'u.id_instit')
            ->where('u.id_instit', $codigoInstituicao)
            ->get();
            
        return $resultados->isNotEmpty();
    }

    /**
     * Verificar se o usuário tem acesso à instituição específica
     */
    public static function getUserByCpf(string $cpf)
    {
        $codigoInstituicao = CODIGOINSTITUICAO;
        $resultados = DB::table('configuracoes.db_usuarios as usuarios')
            ->join('configuracoes.db_usuacgm as usuacgm', 'usuacgm.id_usuario','=','usuarios.id_usuario')
            ->join('protocolo.cgm as cgm', 'cgm.z01_numcgm','=','usuacgm.cgmlogin')
            ->join('pessoal.rhpessoal as rhpessoal', 'cgm.z01_numcgm','=','rhpessoal.rh01_numcgm')
            // ->joinleft('configuracoes.db_userinst as userinst', 'userinst.id_usuario','=','usuarios.id_usuario')
            ->select('usuarios.senha', 'usuarios.id_usuario as codigo', 'cgm.z01_cgccpf as login' )
            ->where('cgm.z01_cgccpf', '=', $cpf)
            ->where('usuext', '=', 1) 
            // ->where('userinst.rh01_instit', '=', $codigoInstituicao)
            ->get();
        return $resultados->first();
    }

    public function usuacgm()
    {
        return $this->hasOne(Usuacgm::class, 'id_usuario');
    }
}
