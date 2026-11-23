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

$string['accessrestricted'] = 'O acesso ao QuickCards está restrito a coortes específicas.';
$string['actions'] = 'Ações';
$string['addcard'] = 'Adicionar Card';
$string['allcourses'] = 'Todos os cursos';
$string['alldone'] = 'Tudo em dia! Nenhuma revisão pendente hoje.';
$string['answer'] = 'Verso (Resposta / Explicação)';
$string['answer_help'] = 'A explicação ou resposta concisa que você deseja memorizar.';
$string['backtodeck'] = 'Voltar ao Baralho';
$string['backtodecks'] = 'Voltar aos Baralhos';
$string['cancel'] = 'Cancelar';
$string['card'] = 'Card';
$string['cardback'] = 'Verso (Resposta)';
$string['cardback_placeholder'] = 'Qual é a resposta ou definição?';
$string['cardcount'] = '{$a} cards';
$string['cardcreated'] = 'Card criado com sucesso.';
$string['carddeleted'] = 'Card excluído com sucesso.';
$string['cardemptyerror'] = 'A frente e o verso do card são obrigatórios.';
$string['cardfront'] = 'Frente (Pergunta)';
$string['cardfront_placeholder'] = 'Qual é a pergunta ou conceito principal?';
$string['cardmoved'] = 'Card movido com sucesso.';
$string['cardof'] = 'Card {$a->current} de {$a->total}';
$string['cards'] = 'Cards';
$string['cardsaddedtodeck'] = '{$a->count} card(s) adicionado(s) a "{$a->name}".';
$string['cardsaved'] = 'Card criado com sucesso!';
$string['cardsinfile'] = '{$a} cards encontrados';
$string['cardupdated'] = 'Card atualizado com sucesso.';
$string['clearfilter'] = 'Limpar filtros';
$string['close'] = 'Fechar';
$string['confirmdeletecard'] = 'Tem certeza de que deseja excluir este card?';
$string['confirmdeletedeck'] = 'Tem certeza de que deseja excluir este baralho? Todos os seus cards e histórico de revisão serão permanentemente excluídos.';
$string['confirmresetdeck'] = 'Tem certeza de que deseja reiniciar o progresso de estudo de "{$a}"? Todos os cards neste baralho voltarão a 0 repetições e serão agendados para revisão hoje.';
$string['congratulations'] = 'Parabéns!';
$string['containsstudyprogress'] = 'Este arquivo inclui progresso pessoal de estudo.';
$string['course'] = 'Curso';
$string['coursedeck'] = 'Curso Vinculado';
$string['coursedecks'] = 'Baralhos deste curso';
$string['createcard'] = 'Adicionar Card';
$string['createdeck'] = 'Criar Baralho';
$string['csvcardsdetected'] = '{$a} cards válidos detectados';
$string['deck'] = 'Baralho';
$string['deckcreated'] = 'Baralho criado com sucesso.';
$string['deckdeleted'] = 'Baralho excluído com sucesso.';
$string['deckdescription'] = 'Descrição';
$string['deckdescription_placeholder'] = 'Anotações opcionais sobre o conteúdo deste baralho...';
$string['deckimported'] = 'Baralho "{$a->name}" com {$a->count} cards importado com sucesso.';
$string['deckname'] = 'Nome do Baralho';
$string['deckname_help'] = 'Dê um nome claro ao seu baralho, como "Anatomia - Músculos" ou "Conceitos de JavaScript".';
$string['deckpreview'] = 'Pré-visualização do Baralho';
$string['deckreset'] = 'Progresso de estudo reiniciado com sucesso.';
$string['decks'] = 'Baralhos';
$string['deckupdated'] = 'Baralho atualizado com sucesso.';
$string['delete'] = 'Excluir';
$string['deletecard'] = 'Excluir Card';
$string['deletedeck'] = 'Excluir Baralho';
$string['delimiter'] = 'Separador';
$string['delimiter_auto'] = 'Autodetectar (Recomendado)';
$string['delimiter_comma'] = 'Vírgula (,)';
$string['delimiter_semicolon'] = 'Ponto e vírgula (;)';
$string['delimiter_tab'] = 'Tabulação (Padrão Anki / Quizlet)';
$string['duplicateaction'] = 'Cartões duplicados';
$string['duplicateaction_help'] = 'O que fazer caso um cartão importado tenha a mesma pergunta que um cartão já existente no baralho.';
$string['duplicateallow'] = 'Permitir duplicados (adicionar mesmo assim)';
$string['duplicateskip'] = 'Ignorar duplicados (manter cartão existente)';
$string['duplicateupdate'] = 'Atualizar existente (substituir resposta)';
$string['duecardsstat'] = 'Pendentes hoje';
$string['duecountbadge'] = '{$a} pendente(s)';
$string['dueforreview'] = 'Revisões Pendentes';
$string['duereviews'] = 'Cards para revisão hoje';
$string['edit'] = 'Editar';
$string['editcard'] = 'Editar Card';
$string['editdeck'] = 'Editar Baralho';
$string['errorcreatingcard'] = 'Erro ao criar cartão.';
$string['errorcreatingdeck'] = 'Erro ao criar baralho.';
$string['exportcontentonly'] = 'Apenas cards (Recomendado para compartilhar)';
$string['exportcontentonlydesc'] = 'Exporta os detalhes e cards sem o seu histórico pessoal de revisões. Ideal para compartilhar com colegas.';
$string['exportcsv'] = 'CSV — Planilha / Anki / Quizlet';
$string['exportcsvdesc'] = 'Exporta perguntas e respostas em formato CSV padrão, compatível com Excel, Anki e Quizlet.';
$string['exportdeck'] = 'Exportar Baralho';
$string['exportdeckfor'] = 'Exportando baralho:';
$string['exportjson'] = 'Baixar Arquivo';
$string['exportwithprogress'] = 'Cards + Progresso de Estudo (Backup pessoal)';
$string['exportwithprogressdesc'] = 'Inclui intervalos SM-2, fatores de facilidade e contagem de repetições. Ideal para backup pessoal.';
$string['filterall'] = 'Todos';
$string['filtercourse'] = 'Filtrar por curso';
$string['filterdue'] = 'Pendentes';
$string['filterlearned'] = 'Aprendidos';
$string['filternew'] = 'Novos';
$string['filterstatus'] = 'Filtrar por status';
$string['flipcard'] = 'Virar card';
$string['generaldeck'] = 'Geral (Não vinculado a um curso)';
$string['importcards'] = 'Importar Cartões';
$string['importcardsfor'] = 'Importando cartões para: {$a}';
$string['importdeck'] = 'Importar Baralho';
$string['importdestination'] = 'Destino da importação';
$string['importexistingdeck'] = 'Adicionar a um baralho existente';
$string['importjson'] = 'Importar JSON';
$string['importnewdeck'] = 'Criar um novo baralho';
$string['importtabcsv'] = 'CSV / Anki / Quizlet';
$string['importtabjson'] = 'QuickCards (JSON)';
$string['invalidfileformat'] = 'O arquivo selecionado não é uma exportação válida do QuickCards.';
$string['invalidjson'] = 'Não foi possível ler o arquivo JSON selecionado.';
$string['inxdays'] = 'Em {$a} dias';
$string['keyboard_shortcuts'] = 'Atalhos: [Espaço] Virar card &bull; [1] Repetir &bull; [2] Difícil &bull; [3] Bom &bull; [4] Fácil';
$string['managecards'] = 'Gerenciar Cards';
$string['movecard'] = 'Mover Card';
$string['moveto'] = 'Mover para Baralho';
$string['mycards'] = 'Meus QuickCards';
$string['myreviews'] = 'Minhas Revisões';
$string['never'] = 'Nunca revisado';
$string['nextreview'] = 'Próxima Revisão';
$string['nocards'] = 'Não há cards neste baralho ainda.';
$string['nocardsdesc'] = 'Adicione cards com conceitos principais, definições ou perguntas para começar a estudar!';
$string['nocardsfound'] = 'Nenhum card encontrado com esses filtros.';
$string['nocoursedecks'] = 'Nenhum baralho vinculado a este curso ainda.';
$string['nodeckname'] = 'O arquivo de exportação não contém um nome de baralho válido.';
$string['nodecks'] = 'Você ainda não criou nenhum baralho.';
$string['nodecksdesc'] = 'Baralhos de flashcards ajudam você a organizar conceitos e estudar com memorização ativa. Crie seu primeiro baralho para começar!';
$string['nodecksfound'] = 'Nenhum baralho encontrado com esses filtros.';
$string['noduecards'] = 'Excelente! Você não tem nenhum card pendente para revisão agora.';
$string['nofileorcard'] = 'O arquivo de exportação não contém nenhum card.';
$string['notargetdecks'] = 'Você não possui outros baralhos para onde mover este card.';
$string['pastecsvdata'] = 'Colar cards (Pergunta e Resposta por linha)';
$string['pastecsvdata_placeholder'] = "O que é mitocôndria?\tCentral de energia da célula\nCapital da França\tParis";
$string['plugindisabled'] = 'O QuickCards está desabilitado neste site.';
$string['pluginname'] = 'QuickCards';
$string['privacy:metadata'] = 'O QuickCards armazena flashcards, baralhos e intervalos de repetição espaçada dos usuários.';
$string['privacy:metadata:block_quickcards_cards'] = 'Armazena os flashcards individuais dentro dos baralhos.';
$string['privacy:metadata:block_quickcards_cards:answer'] = 'A resposta ou explicação no verso do flashcard.';
$string['privacy:metadata:block_quickcards_cards:deckid'] = 'O ID do baralho ao qual o card pertence.';
$string['privacy:metadata:block_quickcards_cards:question'] = 'A pergunta ou enunciado na frente do flashcard.';
$string['privacy:metadata:block_quickcards_cards:timecreated'] = 'A data/hora de criação do card.';
$string['privacy:metadata:block_quickcards_cards:timemodified'] = 'A data/hora da última modificação do card.';
$string['privacy:metadata:block_quickcards_cards:userid'] = 'O ID do usuário criador do card.';
$string['privacy:metadata:block_quickcards_decks'] = 'Armazena os baralhos de flashcards criados pelos usuários.';
$string['privacy:metadata:block_quickcards_decks:courseid'] = 'O ID do curso vinculado ao baralho, ou 0 para baralhos gerais.';
$string['privacy:metadata:block_quickcards_decks:description'] = 'A descrição ou resumo do baralho.';
$string['privacy:metadata:block_quickcards_decks:name'] = 'O nome do baralho de flashcards.';
$string['privacy:metadata:block_quickcards_decks:timecreated'] = 'A data/hora de criação do baralho.';
$string['privacy:metadata:block_quickcards_decks:timemodified'] = 'A data/hora da última alteração do baralho.';
$string['privacy:metadata:block_quickcards_decks:userid'] = 'O ID do usuário proprietário do baralho.';
$string['privacy:metadata:block_quickcards_reviews'] = 'Armazena o progresso de repetição espaçada e histórico de revisões por card e usuário.';
$string['privacy:metadata:block_quickcards_reviews:cardid'] = 'O ID do card revisado.';
$string['privacy:metadata:block_quickcards_reviews:easefactor'] = 'O fator de facilidade do SM-2 que determina o crescimento do intervalo.';
$string['privacy:metadata:block_quickcards_reviews:intervaldays'] = 'O intervalo em dias até a próxima revisão programada.';
$string['privacy:metadata:block_quickcards_reviews:lastreview'] = 'A data/hora da sessão de revisão mais recente.';
$string['privacy:metadata:block_quickcards_reviews:nextreview'] = 'A data/hora da próxima revisão agendada.';
$string['privacy:metadata:block_quickcards_reviews:repetitions'] = 'A contagem de revisões consecutivas com sucesso.';
$string['privacy:metadata:block_quickcards_reviews:userid'] = 'O ID do usuário que realizou o estudo.';
$string['question'] = 'Frente (Pergunta / Enunciado)';
$string['question_help'] = 'A pergunta, termo ou conceito que estimula a recuperação ativa.';
$string['quickaddcard'] = 'Novo card';
$string['quickcards:addinstance'] = 'Adicionar um novo bloco QuickCards';
$string['quickcards:myaddinstance'] = 'Adicionar um novo bloco QuickCards ao Painel';
$string['quickcards:use'] = 'Utilizar repetição espaçada do QuickCards';
$string['rating_again'] = 'Errei / Repetir (1)';
$string['rating_easy'] = 'Fácil (4)';
$string['rating_good'] = 'Bom (3)';
$string['rating_hard'] = 'Difícil (2)';
$string['repetitions'] = 'Repetições';
$string['resetdeck'] = 'Reiniciar Progresso do Baralho';
$string['resetprogress'] = 'Reiniciar Progresso';
$string['restoreprogress'] = 'Restaurar progresso de estudo';
$string['restoreprogress_help'] = 'Se marcado, os intervalos anteriores e fatores de facilidade serão preservados. Se desmarcado, os cards iniciarão como novos.';
$string['reviews'] = 'Revisões';
$string['save'] = 'Salvar';
$string['savecard'] = 'Salvar card';
$string['searchcards'] = 'Buscar cards...';
$string['searchdecks'] = 'Buscar baralhos...';
$string['selectcsvfile'] = 'Enviar arquivo CSV / TXT (opcional)';
$string['selectcsvfile_help'] = 'Envie um arquivo .csv ou .txt, ou cole sua lista diretamente abaixo.';
$string['selectjsonfile'] = 'Selecionar arquivo JSON do QuickCards';
$string['selectjsonfile_help'] = 'Escolha um arquivo .json exportado pelo QuickCards.';
$string['selecttargetdeck'] = 'Selecione o baralho de destino';
$string['sessioncomplete'] = 'Sessão concluída!';
$string['sessioncompletedesc'] = 'Você revisou todos os cards previstos para esta sessão.';
$string['servererror'] = 'Ocorreu um erro no servidor. Por favor, tente novamente.';
$string['setting_allowedcohorts'] = 'Coortes permitidas';
$string['setting_allowedcohorts_desc'] = 'Restringe o uso do QuickCards aos membros das coortes selecionadas. Deixe vazio para permitir todos os usuários com permissão. Administradores do site sempre têm acesso.';
$string['setting_enabled'] = 'Habilitar QuickCards';
$string['setting_enabled_desc'] = 'Se habilitado, usuários com permissão podem acessar flashcards e sessões de estudo. Se desabilitado, o plugin fica inativo.';
$string['setting_nocohorts'] = 'Nenhuma coorte existe neste site ainda. Crie coortes em Administração do site > Usuários > Contas > Coortes para restringir por coorte.';
$string['showanswer'] = 'Mostrar Resposta';
$string['showanswerspace'] = 'Mostrar Resposta (Espaço)';
$string['showingcount'] = 'Exibindo {$a->shown} de {$a->total} cards';
$string['showingdeckscount'] = 'Exibindo {$a->shown} de {$a->total} baralhos';
$string['study'] = 'Estudar';
$string['studyagain'] = 'Estudar Novamente';
$string['studyall'] = 'Estudar Todos os Pendentes';
$string['studydeck'] = 'Estudar {$a}';
$string['targetdeck'] = 'Baralho';
$string['today'] = 'Para hoje';
$string['totalcards'] = 'Total de Cards';
$string['totalcardsstat'] = 'Total de cards';
$string['totaldecks'] = 'Total de Baralhos';
$string['trydifferentsearch'] = 'Tente buscar com palavras-chave diferentes ou limpe os filtros.';
$string['unsupportedversion'] = 'Versão de exportação não suportada: {$a}.';
$string['viewall'] = 'Abrir QuickCards';
$string['withduecards'] = 'Com revisões pendentes';
