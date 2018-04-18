#!/usr/local/rvm/rubies/ruby-1.9.3-p551/bin/ruby

require 'sqlite3'

begin
	
	# Get values from smarty config file
	cfg_path = "/full/path/to/fixity.cfg"
	cfg_arr = File.readlines(cfg_path)
	prefix = /"([^"]*)/.match(cfg_arr[1])[1 .. -1][0]
	dirlist = /"([^"]*)/.match(cfg_arr[2])[1 .. -1][0]
	postfix = /"([^"]*)/.match(cfg_arr[3])[1 .. -1][0]
	log_path = /"([^"]*)/.match(cfg_arr[4])[1 .. -1][0]

	# Format specifier for strftime
	timefstr = "%a %b %d %H:%M:%S.%6N %Y"

	# Redirect stdio/stderr to log file
	$stdout.reopen(log_path, "a")
	$stderr.reopen(log_path, "a")

	# Construct list of paths to fixi.db (dotpaths)
	dotpaths = Array.new()
	dirlist.split(', ').each do |dir|
		paths = "#{prefix}/#{dir.strip}"
		dotpaths.push(paths)
	end
	
	puts "[#{Time.now.strftime(timefstr)}] === BEGIN AUDIT ==="

	# If (finished != 1) is found, then the audit needs resumed. 
	# Add that directory to the resume list. 
	resumes = Array.new()
	dotpaths.each_with_index do |dotpath, index| 
		if Dir.exists?(dotpath)
			db = SQLite3::Database.new(File.join(dotpath, "/#{postfix}"))
			resumeRow = db.get_first_row( "select finished from resume" )
			finished = resumeRow[0]
			if (finished != 1)
					resumes.push(dotpaths[index])	
				break
			end
		else
			print "Path not found: ", dotpath, "\n"
		end
	end

	resumes.each do |rsm|
		print "[#{Time.now.strftime(timefstr)}] Resuming #{rsm}... " 
		cmd = "fixi audit #{rsm}"
		`#{cmd}`
		print "DONE\n"
	end

	dotpaths.each do |rns|	
		print "[#{Time.now.strftime(timefstr)}] Auditing #{rns}... "
		cmd = "fixi audit #{rns}"
		`#{cmd}`
		print "DONE\n"
	end

	puts "[#{Time.now.strftime(timefstr)}] === END AUDIT ==="

end

# vim: set ts=2 sw=2 : 

