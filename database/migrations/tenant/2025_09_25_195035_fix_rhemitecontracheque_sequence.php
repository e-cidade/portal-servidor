<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE rhemitecontracheque
              ALTER COLUMN rh85_sequencial
              SET DEFAULT nextval('rhemitecontracheque_rh85_sequencial_seq')
        ");

        DB::statement("
            ALTER SEQUENCE rhemitecontracheque_rh85_sequencial_seq
              OWNED BY rhemitecontracheque.rh85_sequencial
        ");
    }

    public function down(): void
    {
        // No down() você pode remover o DEFAULT e a relação da sequence se quiser
        DB::statement("
            ALTER TABLE rhemitecontracheque
              ALTER COLUMN rh85_sequencial DROP DEFAULT
        ");

        DB::statement("
            ALTER SEQUENCE rhemitecontracheque_rh85_sequencial_seq
              OWNED BY NONE
        ");
    }
};
