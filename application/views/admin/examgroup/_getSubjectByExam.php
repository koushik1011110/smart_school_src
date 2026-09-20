                            <div class="row pb10">
                              <div class="col-lg-2 col-md-3 col-sm-12">   
                                <p class="examinfo"><span><?php echo $this->lang->line('exam')?></span><?php echo $examgroupDetail->exam; ?></p>
                              </div> 

                              <div class="col-lg-10 col-md-9 col-sm-12">   
                                <p class="examinfo"><span><?php echo $this->lang->line('exam_group'); ?></span><?php echo $examgroupDetail->exam_group_name; ?></p>
                              </div> 
                            </div><!--./row-->
                            <div class="divider2"></div>
                            
                            <div class="box box-solid box-default mb10" style="background:#f9f9f9; padding:10px; border:1px solid #e7e7e7; border-radius:4px;">
                              <h5 class="box-title" style="margin-top:0; margin-bottom:10px; font-weight:bold;"><i class="fa fa-upload"></i> Bulk Upload Exam Marks (All Subjects)</h5>
                              <form id="bulkUploadMarksForm" method="POST" enctype="multipart/form-data" action="<?php echo site_url('admin/examgroup/uploadbulkfile'); ?>">
                                <input type="hidden" name="exam_id" value="<?php echo $examgroupDetail->id; ?>">
                                <div class="row">
                                  <div class="col-md-3 col-sm-6">
                                    <div class="form-group mb5">
                                      <label><?php echo $this->lang->line('class'); ?><small class="req"> *</small></label>
                                      <select id="bulk_class_id" name="class_id" class="form-control input-sm">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php if (!empty($classlist)) { foreach ($classlist as $class) { ?>
                                          <option value="<?php echo $class['id']; ?>"><?php echo $class['class']; ?></option>
                                        <?php } } ?>
                                      </select>
                                    </div>
                                  </div>
                                  <div class="col-md-3 col-sm-6">
                                    <div class="form-group mb5">
                                      <label><?php echo $this->lang->line('section'); ?><small class="req"> *</small></label>
                                      <select id="bulk_section_id" name="section_id" class="form-control input-sm">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                      </select>
                                    </div>
                                  </div>
                                  <div class="col-md-2 col-sm-4">
                                    <div class="form-group mb5">
                                      <label><?php echo $this->lang->line('session'); ?><small class="req"> *</small></label>
                                      <select id="bulk_session_id" name="session_id" class="form-control input-sm">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php if (!empty($sessionlist)) { foreach ($sessionlist as $session) { ?>
                                          <option value="<?php echo $session['id']; ?>" <?php echo ($current_session == $session['id']) ? 'selected="selected"' : ''; ?>><?php echo $session['session']; ?></option>
                                        <?php } } ?>
                                      </select>
                                    </div>
                                  </div>
                                  <div class="col-md-4 col-sm-8">
                                    <div class="form-group mb5">
                                      <label>CSV File<small class="req"> *</small></label>
                                      <input type="file" name="file" id="bulk_file" class="form-control input-sm" style="padding:2px 5px;" accept=".csv">
                                    </div>
                                  </div>
                                </div>
                                <div class="row mt5">
                                  <div class="col-md-12 text-right">
                                    <button type="button" id="btnExportBulkSample" class="btn btn-info btn-xs mr5"><i class="fa fa-download"></i> <?php echo $this->lang->line('export_sample'); ?> (Bulk CSV)</button>
                                    <button type="submit" id="btnBulkUpload" class="btn btn-primary btn-xs" data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('upload'); ?>"><i class="fa fa-upload"></i> <?php echo $this->lang->line('upload'); ?></button>
                                  </div>
                                </div>
                              </form>
                            </div>
                            <div class="divider2"></div>
                            <div class="table-responsive row">
                              <table class="table table-bordered" id="subjects_table">
                                <thead>
                                    <tr>
                                        <th class="col-sm-3"><?php echo $this->lang->line('subject')?></th>
                                        <th class=""><?php echo $this->lang->line('date_from'); ?></th>
                                        <th class=""><?php echo $this->lang->line('start_time'); ?></th>
                                        <th class=""><?php echo $this->lang->line('duration'); ?></th>
                                        <th class=""><?php echo $this->lang->line('room_no'); ?></th>
                                        <th class=""><?php echo $this->lang->line('marks_max'); ?></th>
                                        <th class=""><?php echo $this->lang->line('marks_min'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('enter_marks'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($exam_subjects)) {
                                       
                                        foreach ($exam_subjects as $exam_subject_key => $exam_subject_value) {
                                            ?>
                                            <tr>
                                                <td><?php echo $exam_subject_value->subject_name; ?><?php if($exam_subject_value->subject_code){ echo ' ('.$exam_subject_value->subject_code.')'; } ?></td>
                                                <td><?php echo $this->customlib->dateformat($exam_subject_value->date_from); ?></td>
                                                <td><?php echo $exam_subject_value->time_from; ?></td>
                                                <td><?php echo $exam_subject_value->duration; ?></td>
                                                <td><?php echo $exam_subject_value->room_no; ?></td>
                                                <td><?php echo $exam_subject_value->max_marks; ?></td>
                                                <td><?php echo $exam_subject_value->min_marks; ?></td>
                                                <td class="col-sm-1 text-right">  
                                                    <span data-toggle="tooltip" title="<?php echo $this->lang->line('exam_marks'); ?>">                                          
                                                    <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#subjectModal" data-subject_name="<?php echo $exam_subject_value->subject_name; ?>" data-subject_id="<?php echo $exam_subject_value->id; ?>" data-teachersubject_id="<?php echo $exam_subject_value->subject_id; ?>" ><i class="fa fa-newspaper-o" aria-hidden="true"></i></button></span>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>    