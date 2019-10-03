<?php /*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

    This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<style>
@media (min-width: 1024px) {
    #add-agenda-from-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #add-agenda-from-modal .modal-dialog {
        width: 1300px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="add-agenda-from-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>

        <h4 class="modal-title"><?php echo lang("addAgendaFrom_title"); ?></h4>
      </div>
      <div class="modal-body">
          
        <form class="form-horizontal">
            <fieldset>

                <input id="parentAgendaIdInput" name="parentPointId" type="hidden" placeholder="" class="form-control input-md">
                <input id="meetingIdInput" name="meetingId" type="hidden" value="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>" placeholder="" class="form-control input-md">

                <!-- Label input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="parentAgendaLabel">Point d'agenda parent</label>  
                    <label class="col-md-8 control-label text-left" style="text-align: left;" id="parentAgendaLabel"></label>
                </div>

            </fieldset>
            <fieldset>
                <legend>Source</legend>

                <!-- Select Basic -->
                <div id="meetingSelectDiv" class="form-group">
                    <label class="col-md-4 control-label" for="meetingSelect">Réunion</label>
                    <div class="col-md-8">
                        <select id="meetingSelect" name="meetingId" class="form-control">
                            <option value="0" disabled="disabled" selected="selected"></option>
                            <optgroup label="Discussion et Co-construction" id="construction-group"></optgroup>
                            <optgroup label="Réunion et prise de décisions" id="meeting-group"></optgroup>
                        </select>
                    </div>
                </div>

                <!-- Select Basic -->
                <div id="agendaSelectDiv" class="form-group" style="display: none;">
                    <label class="col-md-4 control-label" for="agendaSelect">Point</label>
                    <div class="col-md-8">
                        <select id="agendaSelect" name="agendaId" class="form-control">
                        </select>
                    </div>
                </div>

                <!-- Select Basic -->
                <div id="motionSelectDiv" class="form-group" style="display: none;">
                    <label class="col-md-4 control-label" for="motionSelect">Motion</label>
                    <div class="col-md-8">
                        <select id="motionSelect" name="motionId" class="form-control">
                        </select>
                    </div>
                </div>

            </fieldset>
            <fieldset>
                <legend>Point</legend>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="titleInput">Titre</label>  
                    <div class="col-md-8">
                        <input id="titleInput" name="title" type="text" placeholder="" class="form-control input-md">
                    </div>
                </div>

                <!-- Textarea -->
                <div id="descriptionDiv" class="form-group" style="display: none;">
                    <label class="col-md-4 control-label" for="descriptionArea">Description</label>
                    <div class="col-md-8">                     
                        <textarea class="form-control autogrow" id="descriptionArea" name="description" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 300px;"></textarea>
                    </div>
                </div>

                <!-- Textarea -->
                <div id="firstChatDiv" class="form-group" style="display: none;">
                    <label class="col-md-4 control-label" for="firstChatArea">Premier Tchat</label>
                    <div class="col-md-8">                     
                        <textarea class="form-control autogrow" id="firstChatArea" name="firstChat" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 300px;"></textarea>
                        <input type="hidden" id="firstChatAuthorInput">
                    </div>
                </div>

                <!-- Textarea -->
                <div id="motionDiv" class="form-group">
                    <label class="col-md-4 control-label" for="motion-btn-group">Motion</label>
                    <div class="col-md-8">
                        <div class="btn-group" role="group" id="motion-btn-group">
                            <button type="button" class="btn btn-success active" value="none" id="no-motion-btn">Aucune</button>
                            <button type="button" class="btn btn-default" value="yesno">Oui-Non</button>
                            <button type="button" class="btn btn-default" value="proagainst">Pour-Contre</button>
                        </div>
                    </div>
                </div>

                <!-- Select Basic -->
                <div id="motionWrapperSelectDiv" class="form-group" style="display: none;">
                    <label class="col-md-4 control-label" for="motionWrapperSelect">Type de motion</label>
                    <div class="col-md-8">
                        <select id="motionWrapperSelect" name="motionWrapper" class="form-control">
                            <option value="" disabled="disabled" selected="selected"></option>
                            
                            <!-- Externalize that into a plugin configuration -->
                            <optgroup label="Equipage" id="equipage-group">
                                <option value="" 
                                    data-title="L'Assemblée Permanente valide la création de l'équipage &quot;Nom de l'équipage&quot;" 
                                    data-description="Pour accéder au code de fonctionnement de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;équipage&quot;,&quot;equipage&quot;]}]">Création d'équipage</option>
                                <option value="" 
                                    data-title="L'Assemblée Permanente valide la modification du code de fonctionnement de l'équipage &quot;Nom de l'équipage&quot;" 
                                    data-description="Pour accéder à la modification du code de fonctionnement de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;équipage&quot;,&quot;equipage&quot;]}]">Modification de code de fonctionnement d'un équipage</option>
                                <option value="" 
                                    data-title="L'Assemblée Permanente valide la demande budgétaire de l'équipage &quot;Nom de l'équipage&quot;" 
                                    data-description="Pour accéder à la demande budgétaire de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;équipage&quot;,&quot;equipage&quot;]}]">Demande budgétaire d'un équipage</option>
                                <option value="" 
                                    data-title="L'Assemblée Permanente valide le rapport de fonctionnement de l'équipage &quot;Nom de l'équipage&quot;" 
                                    data-description="Pour accéder au rapport de fonctionnement de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;rapport&quot;,&quot;fonctionnement&quot;,&quot;équipage&quot;,&quot;equipage&quot;]}]">Validation d'un rapport de fonctionnement</option>
                                <option value="" 
                                    data-title="L'Assemblée Permanente valide la dissolution de l'équipage &quot;Nom de l'équipage&quot;" 
                                    data-description="Pour accéder au rapport de fonctionnement de cet équipage et sa demande de dissolution, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;rapport&quot;,&quot;fonctionnement&quot;,&quot;demande&quot;,&quot;dissolution&quot;,&quot;équipage&quot;,&quot;equipage&quot;]}]">Validation d'une demande de dissolution</option>
                            </optgroup>
                            <optgroup label="Candidature" id="candidate-group">
                                <option value="" 
                                    data-title="L'Assemblée Permanente nomme &quot;le candidat&quot; en tant que porte-parole."
                                    data-description="Pour accéder à sa candidature, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;le candidat&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;candidature&quot;,&quot;de&quot;,&quot;d'&quot;]}]">Candidature au porte-parolat</option>
                                <option value="" 
                                    data-title="L'Assemblée Permanente inscrit &quot;le candidat&quot; au sein de l'équipe nom de l'équipe."
                                    data-description="Pour accéder à sa candidature, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;le candidat&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;candidature&quot;,&quot;de&quot;,&quot;d'&quot;]},{&quot;src&quot;:&quot;nom de l'équipe&quot;,&quot;val&quot;:&quot;parentAgendaLabel&quot;,&quot;rmv&quot;:[&quot;candidatures&quot;,&quot;candidature&quot;,&quot;pour&quot;,&quot;l'&quot;,&quot;equipe&quot;,&quot;équipe&quot;]}]">Candidature à une équipe</option>
                                <option value="" 
                                    data-title="L'Assemblée Permanente inscrit &quot;le candidat&quot; au sein du conseil nom du conseil."
                                    data-description="Pour accéder à sa candidature, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;le candidat&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;candidature&quot;,&quot;de&quot;,&quot;d'&quot;]},{&quot;src&quot;:&quot;nom du conseil&quot;,&quot;val&quot;:&quot;parentAgendaLabel&quot;,&quot;rmv&quot;:[&quot;candidatures&quot;,&quot;candidature&quot;,&quot;pour&quot;,&quot;le&quot;,&quot;conseil&quot;]}]">Candidature à un conseil</option>
                            </optgroup>
                            <optgroup label="Motion simple" id="simple-motion-group">
                                <option value="" 
                                    data-title="Le Parti Pirate signe le texte &quot;Nom du texte&quot;."
                                    data-description="Pour accéder au contenu du texte, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom du texte&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;signature&quot;,&quot;signer&quot;]}]">Signature de texte</option>
                                <option value="" 
                                    data-title="Le Parti Pirate soutient &quot;Nom du soutien&quot;."
                                    data-description="Pour accéder au sujet du soutien, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom du soutien&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[]}]">Soutien</option>
<!--
                                <option value="" 
                                    data-title="Le Parti Pirate signe le texte &quot;Nom du texte&quot;."
                                    data-description="Pour accéder au contenu du texte, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.">Signature de texte</option>
-->                                    
                            </optgroup>
                            <optgroup label="Motion programmatique" id="programmatic-motion-group">
                                <option value="" 
                                    data-title="L'Assemblée permanente ajoute le point programme &quot;Point programme&quot;"
                                    data-description="Pour accéder au point programme, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Point programme&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[]}]">Ajout d'un point programme</option>
                                <option value="" 
                                    data-title="L'Assemblée permanente supprime le point programme &quot;Point programme&quot;"
                                    data-description="Pour accéder à l'argumentaire, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Point programme&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;suppression&quot;,&quot;point&quot;,&quot;programme&quot;]}]">Suppression d'un point programme</option>
<!--
                                <option value="" 
                                    data-title="Le Parti Pirate signe le texte &quot;Nom du texte&quot;."
                                    data-description="Pour accéder au contenu du texte, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.">Signature de texte</option>
-->                                    
                            </optgroup>
                            <optgroup label="Motion statutaire" id="simple-motion-group">
                                <option value="" 
                                    data-title="L'Assemblée Statutaire valide la modification des statuts avec l'amendement &quot;Nom du texte&quot;"
                                    data-description="Pour accéder à l'amendement, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page."
                                    data-replace="[{&quot;src&quot;:&quot;Nom du texte&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[]}]">Amendement statutaire</option>
<!--
                                <option value="" 
                                    data-title="Le Parti Pirate signe le texte &quot;Nom du texte&quot;."
                                    data-description="Pour accéder au contenu du texte, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.">Signature de texte</option>
-->                                    
                            </optgroup>
                            <option value="" data-title="" data-description="">Autre...</option>
                        </select>
                    </div>
                </div>

                <!-- Textarea -->
                <div id="motionTitleDiv" class="form-group" style="display: none;">
                    <label class="col-md-4 control-label" for="motionTitleArea">Titre de la motion</label>
                    <div class="col-md-8">                     
                        <textarea class="form-control autogrow" id="motionTitleArea" name="motionTitle" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 300px;"></textarea>
                    </div>
                </div>

                <!-- Textarea -->
                <div id="motionDescriptionDiv" class="form-group" style="display: none;">
                    <label class="col-md-4 control-label" for="motionDescriptionArea">Description de la motion</label>
                    <div class="col-md-8">                     
                        <textarea class="form-control autogrow" id="motionDescriptionArea" name="motionDescription" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 300px;"></textarea>
                    </div>
                </div>

            </fieldset>
        </form>          
          
      </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>
        <button type="button" class="btn btn-primary btn-add-agenda-from"><?php echo lang("common_add"); ?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
