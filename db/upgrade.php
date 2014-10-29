<?php
function xmldb_block_dukreminder_upgrade($oldversion) {
	global $DB,$CFG;
	$dbman = $DB->get_manager();
	$result=true;
	
    if ($oldversion < 2014082100) {

        // Define table block_dukreminder_mailssent to be created.
        $table = new xmldb_table('block_dukreminder_mailssent');

        // Adding fields to table block_dukreminder_mailssent.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('reminderid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_dukreminder_mailssent.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_dukreminder_mailssent.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Dukreminder savepoint reached.
        upgrade_block_savepoint(true, 2014082100, 'dukreminder');
    }
    if ($oldversion < 2014101400) {

        // Define field text_teacher to be added to block_dukreminder.
        $table = new xmldb_table('block_dukreminder');
        $field = new xmldb_field('text_teacher', XMLDB_TYPE_TEXT, null, null, null, null, null, 'sent');

        // Conditionally launch add field text_teacher.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Dukreminder savepoint reached.
        upgrade_block_savepoint(true, 2014101400, 'dukreminder');
    }
    if ($oldversion < 2014101401) {
    
    	// Define field daterelative_completion to be added to block_dukreminder.
    	$table = new xmldb_table('block_dukreminder');
    	$field = new xmldb_field('daterelative_completion', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'text_teacher');
    
    	// Conditionally launch add field daterelative_completion.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Dukreminder savepoint reached.
    	upgrade_block_savepoint(true, 2014101401, 'dukreminder');
    }
    
    if ($oldversion < 2014102800) {
    
    	$old_completion_entries = $DB->get_records_select('block_dukreminder','daterelative_completion > 0');
    	
    	$old_enrol_entries = $DB->get_records_select('block_dukreminder','daterelative_completion = 0 OR daterelative_completion is NULL');
    	
    	// Rename field criteria on table block_dukreminder to NEWNAMEGOESHERE.
    	$table = new xmldb_table('block_dukreminder');
    	$field = new xmldb_field('daterelative_completion', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'text_teacher');
    
    	// Launch rename field criteria.
    	$dbman->rename_field($table, $field, 'criteria');
    
    	$field = new xmldb_field('criteria', XMLDB_TYPE_INTEGER, '10', null, null, null, '250001', 'text_teacher');
    	
    	// Launch change of default for field criteria.
    	$dbman->change_field_default($table, $field);
    	
    	foreach($old_completion_entries as $old) {
    		$old->criteria = 250000;
    		$old->daterelative = $old->daterelative_completion;
    		$DB->update_record('block_dukreminder', $old);
    	}
    	
    	foreach($old_enrol_entries as $old) {
    		$old->criteria = 250001;
    		$DB->update_record('block_dukreminder', $old);
    	}
    	
    	// Dukreminder savepoint reached.
    	upgrade_block_savepoint(true, 2014102800, 'dukreminder');
    }
}