<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Courses | Quilana</title>
    <style>
    .course-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .course-card {
        background-color: #ffffff;
        border: 1px solid #090909;
        border-radius: 5px;
        width: 20%;
        margin-bottom: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        flex-grow: 1;
    }

    .course-card-body {
        padding: 15px;
    }

    .course-card-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .course-card-text {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
    }

    .course-actions {
        display: flex;
        justify-content: space-between;
        gap: 5px; /* Adjust this value to reduce the space between buttons */
    }

    .course-actions .btn {
        font-size: 14px;
        padding: 5px 10px; /* Adjust padding for a more compact button */
        flex-grow: 1;
        text-align: center;
    }

    .add-course-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .add-course-btn {
        background-color: #4A4CA6;
    }

    .search-bar {
        display: flex;
        align-items: center;
        flex-grow: 1;
        justify-content: flex-end;
    }

    .search-bar input[type="text"] {
        padding: 5px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-right: 5px;
    }

    .search-bar button {
        padding: 5px 10px;
        font-size: 14px;
        border: none;
        background-color: #4A4CA6;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    .search-bar button:hover {
        background-color: #1E1A43;
    }

    @media (max-width: 768px) {
        .course-card {
            width: 45%; /* Adjust width for smaller screens */
        }

        .add-course-btn {
            margin-left: 0;
            width: 100%; /* Ensure button spans the full width on small screens */
            margin-bottom: 10px;
        }

        .search-bar {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .course-card {
            width: 100%; /* Full width for extra small screens */
        }
    }
</style>

</head>
<body>
    <?php include('nav_bar.php') ?>
    
    <div class="container-fluid admin">
        <div class="add-course-container">
            <button class="btn btn-primary btn-sm add-course-btn" id="new_course"><i class="fa fa-plus"></i> Add Course</button>
            <div class="search-bar">
                <form action="search_courses.php" method="GET">
                    <input type="text" name="query" placeholder="Search courses..." required>
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>
        <div class="col-md-12 p-0 font-weight-bold mb-3">Courses</div>
        <br><br>
        <div class="course-container">
            <?php
            $qry = $conn->query("SELECT * FROM course WHERE faculty_id = '".$_SESSION['login_id']."' ORDER BY course_name ASC");
            if ($qry->num_rows > 0) {
                while ($row = $qry->fetch_assoc()) {
            ?>
            <div class="course-card">
                <div class="course-card-body">
                    <div class="course-card-title"><?php echo $row['course_name'] ?></div>
                    <div class="course-card-text"><?php echo $row['class_id'] ?> Class(es)</div>
                    <div class="course-actions">
                        <button class="btn btn-outline-primary btn-sm manage_course" data-id="<?php echo $row['course_id'] ?>" type="button">Classes</button>
                        <button class="btn btn-primary btn-sm" type="button">View Details</button>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            ?>
        </div>
    </div>

    <!-- Manage Course Modal -->
    <div class="modal fade" id="manage_course" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Add New Course</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id='course-frm'>
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="hidden" name="course_id" />
                            <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                            <input type="text" name="course_name" required="required" class="form-control" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('#new_course').click(function(){
                $('#msg').html('')
                $('#manage_course .modal-title').html('Add New Course')
                $('#manage_course #course-frm').get(0).reset()
                $('#manage_course').modal('show')
            });

            $('.manage_course').click(function(){
                var id = $(this).attr('data-id');
                window.location.href = './manage_course.php?id=' + id;
            });
            
            $('#course-frm').submit(function(e){
                e.preventDefault();
                $('#course-frm [name="save"]').attr('disabled',true)
                $('#course-frm [name="save"]').html('Saving...')
                $('#msg').html('')

                $.ajax({
                    url:'./save_course.php',
                    method:'POST',
                    data:$(this).serialize(),
                    error:err=>{
                        console.log(err)
                        alert('An error occurred')
                        $('#course-frm [name="save"]').removeAttr('disabled')
                        $('#course-frm [name="save"]').html('Save')
                    },
                    success:function(resp){
                        if(typeof resp != undefined){
                            resp = JSON.parse(resp)
                            if(resp.status == 1){
                                alert('Data successfully saved');
                                location.reload()
                            }else{
                                $('#msg').html('<div class="alert alert-danger">'+resp.msg+'</div>')
                            }
                        }
                    }
                })
            });
        });
    </script>
</body>
</html>
