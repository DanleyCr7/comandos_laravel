<?php
public function relatoriosInadimplentesAnual()
    {
        ini_set('memory_limit', -1);
        // SELECT filtra somente os campos que eu quero
        // LEFTJOIN uni tabela com outra busca pelo id que relaciona as duas
        // WHERE filtra os dados pelos campos pela condição que eu escolher na coluna
        // toArray() filtra somente os campos isso serve mais para mostrar em view
        // loadView() passa a view e os dados da varivel que eu tratei os dados em sql para trabalhar no layout do PDF
        // orderBy ordena da em order descrescente ou crescente por exemplo 'ASC' para ordernar em ordem alfabetica.
        // return $pdf->stream(); converte a pagina em pdf
        $inadimplentesAnual = Prestacao::
            select([
                'alunos.id',
                DB::raw('trim(turmas.descricao) AS TURMA'),
                'alunos.nomecompleto',
                'matriculas.aluno_id',
                'prestacoes.id',
                'prestacoes.situacao',
                'prestacoes.referencia'
            ])
            ->leftJoin('matriculas', 'matriculas.id', '=', 'prestacoes.matricula_id')
            ->leftJoin('turmas', 'turmas.id', '=', 'matriculas.turma_id')
            ->leftJoin('alunos', 'alunos.id', '=', 'matriculas.aluno_id')
            ->whereDate('prestacoes.referencia', '<', \Carbon\Carbon::now()->format('Y-m-d'))
            ->where('prestacoes.situacao', 0)
            ->where('matriculas.ano_id', Auth::user()->ano_id)
            ->orderBy('TURMA', 'asc')
            ->orderBy('alunos.nomecompleto', 'asc')
            ->get()
            ->toArray();

        $escola     = Configuracao::get()->first();
        $pdf = PDF::loadHTML('');
        $pdf->loadView('turmas.relatorios-inadimplentes-anual', ['alunos' => $inadimplentesAnual]);
        // $pdf->setOption('orientation', 'landscape');
        $pdf->setOption('header-html', App::make('_.url') .'/matriculas/header/' . $escola->id);
        $pdf->setOption('footer-html', App::make('_.url') . '/matriculas/footer-paginacao');
        // $pdf->setOption('javascript-delay', 2000);
        // $pdf->setOption('enable-javascript', true);
        // $pdf->setOption('no-stop-slow-scripts', true);
        $pdf->setOption('header-spacing', 2);
        $pdf->setOption('margin-top', 55);
        $pdf->setOption('margin-left', 5);
        $pdf->setOption('margin-right', 5);
        $pdf->setOption('title', 'RELATÓRIO DE INADIMPLENTES ANUAL');
        return $pdf->stream();
    }
?>