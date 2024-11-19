<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id(); // ID da tabela
            $table->string('street', 255); // Rua
            $table->string('number', 10); // Número
            $table->string('complement', 255)->nullable(); // Complemento (opcional)
            $table->string('neighbourhood', 255); // Bairro
            $table->string('city', 255); // Cidade
            $table->string('state', 2); // Estado
            $table->string('postal_code', 10); // CEP
            $table->unsignedBigInteger('client_id'); // Relacionamento com clientes
            $table->timestamps(); // created_at e updated_at

            // Chave estrangeira
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade'); // Remove os endereços quando o cliente for excluído
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};
