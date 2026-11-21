<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Language strings (pt_br) for block_quickcards.
 *
 * @package     block_quickcards
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'QuickCards';
$string['quickcards:addinstance'] = 'Adicionar um novo bloco QuickCards';
$string['quickcards:myaddinstance'] = 'Adicionar um novo bloco QuickCards ao Painel';
$string['quickcards:use'] = 'Utilizar repetição espaçada do QuickCards';

// Navegação & Geral.
$string['mycards'] = 'Meus QuickCards';
$string['decks'] = 'Baralhos';
$string['study'] = 'Estudar';
$string['reviews'] = 'Revisões';
$string['totaldecks'] = 'Total de Baralhos';
$string['totalcards'] = 'Total de Cards';
$string['dueforreview'] = 'Revisões Pendentes';
$string['studyall'] = 'Estudar Todos os Pendentes';
$string['managecards'] = 'Gerenciar Cards';
$string['actions'] = 'Ações';
$string['course'] = 'Curso';
$string['save'] = 'Salvar';
$string['cancel'] = 'Cancelar';
$string['close'] = 'Fechar';
$string['delete'] = 'Excluir';
$string['edit'] = 'Editar';

// Widget do Bloco.
$string['duecountbadge'] = '{$a} pendente(s)';
$string['alldone'] = 'Tudo em dia! Nenhuma revisão pendente hoje.';
$string['viewall'] = 'Abrir QuickCards';
$string['coursedecks'] = 'Baralhos deste curso';
$string['myreviews'] = 'Minhas Revisões';
$string['totalcardsstat'] = 'Total de cards';
$string['duecardsstat'] = 'Pendentes hoje';
$string['quickaddcard'] = 'Novo card';
$string['targetdeck'] = 'Baralho';
$string['cardfront'] = 'Frente (Pergunta)';
$string['cardfront_placeholder'] = 'Qual é a pergunta ou conceito principal?';
$string['cardback'] = 'Verso (Resposta)';
$string['cardback_placeholder'] = 'Qual é a resposta ou definição?';
$string['savecard'] = 'Salvar card';
$string['cardsaved'] = 'Card criado com sucesso!';
$string['cardemptyerror'] = 'A frente e o verso do card são obrigatórios.';

// Baralhos.
$string['deck'] = 'Baralho';
$string['createdeck'] = 'Criar Baralho';
$string['editdeck'] = 'Editar Baralho';
$string['deletedeck'] = 'Excluir Baralho';
$string['deckname'] = 'Nome do Baralho';
$string['deckname_help'] = 'Dê um nome claro ao seu baralho, como "Anatomia - Músculos" ou "Conceitos de JavaScript".';
$string['deckdescription'] = 'Descrição';
$string['nodecks'] = 'Você ainda não criou nenhum baralho.';
$string['nodecksdesc'] = 'Baralhos de flashcards ajudam você a organizar conceitos e estudar com memorização ativa. Crie seu primeiro baralho para começar!';
$string['coursedeck'] = 'Curso Vinculado';
$string['generaldeck'] = 'Geral (Não vinculado a um curso)';
$string['confirmdeletedeck'] = 'Tem certeza de que deseja excluir este baralho? Todos os seus cards e histórico de revisão serão permanentemente excluídos.';
$string['resetprogress'] = 'Reiniciar Progresso';
$string['resetdeck'] = 'Reiniciar Progresso do Baralho';
$string['confirmresetdeck'] = 'Tem certeza de que deseja reiniciar o progresso de estudo de "{$a}"? Todos os cards neste baralho voltarão a 0 repetições e serão agendados para revisão hoje.';
$string['deckreset'] = 'Progresso de estudo reiniciado com sucesso.';
$string['deckcreated'] = 'Baralho criado com sucesso.';
$string['deckupdated'] = 'Baralho atualizado com sucesso.';
$string['deckdeleted'] = 'Baralho excluído com sucesso.';

// Cards.
$string['card'] = 'Card';
$string['cards'] = 'Cards';
$string['addcard'] = 'Adicionar Card';
$string['createcard'] = 'Adicionar Card';
$string['editcard'] = 'Editar Card';
$string['deletecard'] = 'Excluir Card';
$string['question'] = 'Frente (Pergunta / Enunciado)';
$string['question_help'] = 'A pergunta, termo ou conceito que estimula a recuperação ativa.';
$string['answer'] = 'Verso (Resposta / Explicação)';
$string['answer_help'] = 'A explicação ou resposta concisa que você deseja memorizar.';
$string['nocards'] = 'Não há cards neste baralho ainda.';
$string['nocardsdesc'] = 'Adicione cards com conceitos principais, definições ou perguntas para começar a estudar!';
$string['cardcount'] = '{$a} cards';
$string['confirmdeletecard'] = 'Tem certeza de que deseja excluir este card?';
$string['cardcreated'] = 'Card criado com sucesso.';
$string['cardupdated'] = 'Card atualizado com sucesso.';
$string['carddeleted'] = 'Card excluído com sucesso.';
$string['movecard'] = 'Mover Card';
$string['moveto'] = 'Mover para Baralho';
$string['selecttargetdeck'] = 'Selecione o baralho de destino';
$string['cardmoved'] = 'Card movido com sucesso.';
$string['notargetdecks'] = 'Você não possui outros baralhos para onde mover este card.';
$string['nextreview'] = 'Próxima Revisão';
$string['never'] = 'Nunca revisado';
$string['today'] = 'Para hoje';
$string['inxdays'] = 'Em {$a} dias';

// Estudo & Revisão.
$string['studydeck'] = 'Estudar {$a}';
$string['duereviews'] = 'Cards para revisão hoje';
$string['noduecards'] = 'Excelente! Você não tem nenhum card pendente para revisão agora.';
$string['showanswer'] = 'Mostrar Resposta';
$string['showanswerspace'] = 'Mostrar Resposta (Espaço)';
$string['flipcard'] = 'Virar card';
$string['rating_again'] = 'Errei / Repetir (1)';
$string['rating_hard'] = 'Difícil (2)';
$string['rating_good'] = 'Bom (3)';
$string['rating_easy'] = 'Fácil (4)';
$string['sessioncomplete'] = 'Sessão concluída!';
$string['sessioncompletedesc'] = 'Você revisou todos os cards previstos para esta sessão.';
$string['backtodecks'] = 'Voltar aos Baralhos';
$string['backtodeck'] = 'Voltar ao Baralho';
$string['cardof'] = 'Card {$a->current} de {$a->total}';
$string['congratulations'] = 'Parabéns!';
$string['studyagain'] = 'Estudar Novamente';
$string['keyboard_shortcuts'] = 'Atalhos: [Espaço] Virar card &bull; [1] Repetir &bull; [2] Difícil &bull; [3] Bom &bull; [4] Fácil';

// Busca & Filtros.
$string['searchcards'] = 'Buscar cards...';
$string['searchdecks'] = 'Buscar baralhos...';
$string['filterstatus'] = 'Filtrar por status';
$string['filtercourse'] = 'Filtrar por curso';
$string['filterall'] = 'Todos';
$string['filterdue'] = 'Pendentes';
$string['filternew'] = 'Novos';
$string['filterlearned'] = 'Aprendidos';
$string['allcourses'] = 'Todos os cursos';
$string['withduecards'] = 'Com revisões pendentes';
$string['nocardsfound'] = 'Nenhum card encontrado com esses filtros.';
$string['nodecksfound'] = 'Nenhum baralho encontrado com esses filtros.';
$string['trydifferentsearch'] = 'Tente buscar com palavras-chave diferentes ou limpe os filtros.';
$string['clearfilter'] = 'Limpar filtros';
$string['showingcount'] = 'Exibindo {$a->shown} de {$a->total} cards';
$string['showingdeckscount'] = 'Exibindo {$a->shown} de {$a->total} baralhos';

// Exportação & Importação.
$string['exportdeck'] = 'Exportar Baralho';
$string['exportdeckfor'] = 'Exportando baralho:';
$string['exportcontentonly'] = 'Apenas cards (Recomendado para compartilhar)';
$string['exportcontentonlydesc'] = 'Exporta os detalhes e cards sem o seu histórico pessoal de revisões. Ideal para compartilhar com colegas.';
$string['exportwithprogress'] = 'Cards + Progresso de Estudo (Backup pessoal)';
$string['exportwithprogressdesc'] = 'Inclui intervalos SM-2, fatores de facilidade e contagem de repetições. Ideal para backup pessoal.';
$string['exportjson'] = 'Baixar Arquivo';
$string['exportcsv'] = 'CSV — Planilha / Anki / Quizlet';
$string['exportcsvdesc'] = 'Exporta perguntas e respostas em formato CSV padrão, compatível com Excel, Anki e Quizlet.';
$string['importdeck'] = 'Importar Baralho';
$string['importjson'] = 'Importar JSON';
$string['importtabjson'] = 'QuickCards (JSON)';
$string['importtabcsv'] = 'CSV / Anki / Quizlet';
$string['selectcsvfile'] = 'Enviar arquivo CSV / TXT (opcional)';
$string['selectcsvfile_help'] = 'Envie um arquivo .csv ou .txt, ou cole sua lista diretamente abaixo.';
$string['pastecsvdata'] = 'Colar cards (Pergunta e Resposta por linha)';
$string['pastecsvdata_placeholder'] = "O que é mitocôndria?\tCentral de energia da célula\nCapital da França\tParis";
$string['delimiter'] = 'Separador';
$string['delimiter_auto'] = 'Autodetectar (Recomendado)';
$string['delimiter_tab'] = 'Tabulação (Padrão Anki / Quizlet)';
$string['delimiter_semicolon'] = 'Ponto e vírgula (;)';
$string['delimiter_comma'] = 'Vírgula (,)';
$string['csvcardsdetected'] = '{$a} cards válidos detectados';
$string['selectjsonfile'] = 'Selecionar arquivo JSON do QuickCards';
$string['selectjsonfile_help'] = 'Escolha um arquivo .json exportado pelo QuickCards.';
$string['deckpreview'] = 'Pré-visualização do Baralho';
$string['cardsinfile'] = '{$a} cards encontrados';
$string['containsstudyprogress'] = 'Este arquivo inclui progresso pessoal de estudo.';
$string['restoreprogress'] = 'Restaurar progresso de estudo';
$string['restoreprogress_help'] = 'Se marcado, os intervalos anteriores e fatores de facilidade serão preservados. Se desmarcado, os cards iniciarão como novos.';
$string['deckimported'] = 'Baralho "{$a->name}" com {$a->count} cards importado com sucesso.';
$string['invalidfileformat'] = 'O arquivo selecionado não é uma exportação válida do QuickCards.';
$string['invalidjson'] = 'Não foi possível ler o arquivo JSON selecionado.';
$string['nodeckname'] = 'O arquivo de exportação não contém um nome de baralho válido.';
$string['nofileorcard'] = 'O arquivo de exportação não contém nenhum card.';
$string['unsupportedversion'] = 'Versão de exportação não suportada: {$a}.';

// Configurações do administrador.
$string['setting_enabled'] = 'Habilitar QuickCards';
$string['setting_enabled_desc'] = 'Se habilitado, usuários com permissão podem acessar flashcards e sessões de estudo. Se desabilitado, o plugin fica inativo.';
$string['setting_allowedcohorts'] = 'Coortes permitidas';
$string['setting_allowedcohorts_desc'] = 'Restringe o uso do QuickCards aos membros das coortes selecionadas. Deixe vazio para permitir todos os usuários com permissão. Administradores do site sempre têm acesso.';
$string['setting_nocohorts'] = 'Nenhuma coorte existe neste site ainda. Crie coortes em Administração do site > Usuários > Contas > Coortes para restringir por coorte.';
$string['plugindisabled'] = 'O QuickCards está desabilitado neste site.';
$string['accessrestricted'] = 'O acesso ao QuickCards está restrito a coortes específicas.';

// Metadados da Privacy API.
$string['privacy:metadata'] = 'O QuickCards armazena flashcards, baralhos e intervalos de repetição espaçada dos usuários.';
$string['privacy:metadata:block_quickcards_decks'] = 'Armazena os baralhos de flashcards criados pelos usuários.';
$string['privacy:metadata:block_quickcards_decks:userid'] = 'O ID do usuário proprietário do baralho.';
$string['privacy:metadata:block_quickcards_decks:courseid'] = 'O ID do curso vinculado ao baralho, ou 0 para baralhos gerais.';
$string['privacy:metadata:block_quickcards_decks:name'] = 'O nome do baralho de flashcards.';
$string['privacy:metadata:block_quickcards_decks:description'] = 'A descrição ou resumo do baralho.';
$string['privacy:metadata:block_quickcards_decks:timecreated'] = 'A data/hora de criação do baralho.';
$string['privacy:metadata:block_quickcards_decks:timemodified'] = 'A data/hora da última alteração do baralho.';

$string['privacy:metadata:block_quickcards_cards'] = 'Armazena os flashcards individuais dentro dos baralhos.';
$string['privacy:metadata:block_quickcards_cards:deckid'] = 'O ID do baralho ao qual o card pertence.';
$string['privacy:metadata:block_quickcards_cards:userid'] = 'O ID do usuário criador do card.';
$string['privacy:metadata:block_quickcards_cards:question'] = 'A pergunta ou enunciado na frente do flashcard.';
$string['privacy:metadata:block_quickcards_cards:answer'] = 'A resposta ou explicação no verso do flashcard.';
$string['privacy:metadata:block_quickcards_cards:timecreated'] = 'A data/hora de criação do card.';
$string['privacy:metadata:block_quickcards_cards:timemodified'] = 'A data/hora da última modificação do card.';

$string['privacy:metadata:block_quickcards_reviews'] = 'Armazena o progresso de repetição espaçada e histórico de revisões por card e usuário.';
$string['privacy:metadata:block_quickcards_reviews:cardid'] = 'O ID do card revisado.';
$string['privacy:metadata:block_quickcards_reviews:userid'] = 'O ID do usuário que realizou o estudo.';
$string['privacy:metadata:block_quickcards_reviews:easefactor'] = 'O fator de facilidade do SM-2 que determina o crescimento do intervalo.';
$string['privacy:metadata:block_quickcards_reviews:intervaldays'] = 'O intervalo em dias até a próxima revisão programada.';
$string['privacy:metadata:block_quickcards_reviews:repetitions'] = 'A contagem de revisões consecutivas com sucesso.';
$string['privacy:metadata:block_quickcards_reviews:nextreview'] = 'A data/hora da próxima revisão agendada.';
$string['privacy:metadata:block_quickcards_reviews:lastreview'] = 'A data/hora da sessão de revisão mais recente.';
