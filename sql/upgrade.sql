DROP TRIGGER fiche_detail_upd_trg ON fiche_detail;

CREATE TRIGGER fiche_detail_upd_trg
  after UPDATE
  ON fiche_detail
  FOR EACH ROW
  EXECUTE PROCEDURE comptaproc.fiche_detail_qcode_upd();

insert into menu_ref(me_code,me_file,me_menu,me_description,me_type) 
values ('RAW:receipt','export_receipt.php','Export la pièce','export la pièce justificative d''une opération','PR');

insert into profile_menu (me_code,p_id,p_type_display) select 'RAW:receipt',p_id,'P' from profile where p_id > 0;


insert into menu_ref(me_code,me_file,me_menu,me_description,me_type) 
values ('RAW:document','export_document.php','Export le document','export le document d''une événement','PR');

insert into profile_menu (me_code,p_id,p_type_display) select 'RAW:document',p_id,'P' from profile where p_id > 0;