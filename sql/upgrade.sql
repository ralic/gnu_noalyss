begin;
 insert into parameter(pr_id,pr_value) values ('MY_CHECK_PERIODE','Y');
 alter table quant_sold drop qs_valid;
 alter table jrn add jr_mt text ;
 update jrn set jr_mt=  extract (microseconds from jr_tech_date);
-- alter table jrn alter jr_mt set not null;
 create   index x_mt on jrn(jr_mt);
 commit;
