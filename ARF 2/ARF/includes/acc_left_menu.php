				<? $username=$auth->getUser(); ?>
                <div style="border-bottom: 1px dotted #AAA; font-size: 8pt; width: 90%; margin-bottom: 15px; padding-bottom: 2px"><? echo getGreeting(); ?>, <? echo $username ?>!</div>
            	<p>
                	<a href="../ARF/">Home</a>
                </p>
            	<p>
                    <strong>Add/Edit</strong><br />
                    <? if(isAdmin($username)){ ?>
                    <a href="admin/people.php">People</a><br />
                    <? } ?>
                    <a href="admin/substitute_signer.php">Substitute Signer</a>
                </p>
                <p>
                    <strong>View</strong><br />
                    <a href="admin/forms.php">Your Forms</a><br />
                    <? /* <a href="admin/forms_to_authorize.php">Forms to Authorize</a> */ ?>
                </p>
                <p>
                    <strong>Notifications</strong><br />
                    <a href="admin/forms_status.php">Check Forms Status</a><br />
                </p>

                <div style="width: 175px; padding-top: 10px; padding-bottom: 10px; margin-top: 10px; border-top: 1px dotted #AAA; border-bottom: 1px dotted #AAA">
                    <form method="GET" action="admin/search.php" class="search_box">
                        <input type="text" name="q" required placeholder="Search for a form..."<? if($keyword!=""){ echo " value=\"$keyword\""; } ?> />
                        <button type="submit">Find</button>
                    </form>
                    <div style="clear: both"></div>
                </div>

                <p>
                	<a href="https://forms.latech.edu/login/?logout=1">Logout</a>
                </p>