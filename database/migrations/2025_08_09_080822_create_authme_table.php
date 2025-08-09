<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Создаем таблицу authme только если она не существует
        if (!Schema::hasTable('authme')) {
            Schema::create('authme', function (Blueprint $table) {
                $table->id();
                $table->string('username', 255)->unique();
                $table->string('realname', 255)->nullable();
                $table->string('password', 255);
                $table->string('ip', 40)->nullable();
                $table->bigInteger('lastlogin')->nullable();
                $table->double('x', 8, 2)->default(0);
                $table->double('y', 8, 2)->default(0);
                $table->double('z', 8, 2)->default(0);
                $table->string('world', 255)->default('world');
                $table->bigInteger('regdate')->default(0);
                $table->string('regip', 40)->nullable();
                $table->float('yaw', 8, 2)->default(0);
                $table->float('pitch', 8, 2)->default(0);
                $table->string('email', 255)->nullable();
                $table->boolean('isLogged')->default(false);
                $table->boolean('hasSession')->default(false);
                $table->string('totp', 32)->nullable();

                // Индексы
                $table->index('username');
                $table->index('ip');
                $table->index('email');
            });
        } else {
            // Если таблица существует, проверяем и добавляем недостающие колонки
            Schema::table('authme', function (Blueprint $table) {
                if (!Schema::hasColumn('authme', 'id') && !Schema::hasColumn('authme', 'username')) {
                    // Если нет ни id, ни username, добавляем id как primary key
                    $table->id()->first();
                }

                if (!Schema::hasColumn('authme', 'realname')) {
                    $table->string('realname', 255)->nullable();
                }

                if (!Schema::hasColumn('authme', 'ip')) {
                    $table->string('ip', 40)->nullable();
                }

                if (!Schema::hasColumn('authme', 'lastlogin')) {
                    $table->bigInteger('lastlogin')->nullable();
                }

                if (!Schema::hasColumn('authme', 'x')) {
                    $table->double('x', 8, 2)->default(0);
                }

                if (!Schema::hasColumn('authme', 'y')) {
                    $table->double('y', 8, 2)->default(0);
                }

                if (!Schema::hasColumn('authme', 'z')) {
                    $table->double('z', 8, 2)->default(0);
                }

                if (!Schema::hasColumn('authme', 'world')) {
                    $table->string('world', 255)->default('world');
                }

                if (!Schema::hasColumn('authme', 'regdate')) {
                    $table->bigInteger('regdate')->default(0);
                }

                if (!Schema::hasColumn('authme', 'regip')) {
                    $table->string('regip', 40)->nullable();
                }

                if (!Schema::hasColumn('authme', 'yaw')) {
                    $table->float('yaw', 8, 2)->default(0);
                }

                if (!Schema::hasColumn('authme', 'pitch')) {
                    $table->float('pitch', 8, 2)->default(0);
                }

                if (!Schema::hasColumn('authme', 'email')) {
                    $table->string('email', 255)->nullable();
                }

                if (!Schema::hasColumn('authme', 'isLogged')) {
                    $table->boolean('isLogged')->default(false);
                }

                if (!Schema::hasColumn('authme', 'hasSession')) {
                    $table->boolean('hasSession')->default(false);
                }

                if (!Schema::hasColumn('authme', 'totp')) {
                    $table->string('totp', 32)->nullable();
                }
            });

            // Добавляем индексы если их нет
            $indexInfo = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('authme');
            $indexNames = array_keys($indexInfo);

            if (!in_array('authme_username_index', $indexNames) && !in_array('authme_username_unique', $indexNames)) {
                Schema::table('authme', function (Blueprint $table) {
                    $table->index('username');
                });
            }

            if (!in_array('authme_ip_index', $indexNames)) {
                Schema::table('authme', function (Blueprint $table) {
                    $table->index('ip');
                });
            }

            if (!in_array('authme_email_index', $indexNames)) {
                Schema::table('authme', function (Blueprint $table) {
                    $table->index('email');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authme');
    }
};
