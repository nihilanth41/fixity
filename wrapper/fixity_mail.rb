#!/usr/local/rvm/rubies/ruby-1.9.3-p551/bin/ruby

require 'sqlite3'
require 'rubygems'
require 'mail'

begin

	# Get values from smarty config file
  cfg_path = "/path/to/fixity.cfg"
	cfg_arr = File.readlines(cfg_path)
	prefix = /"([^"]*)/.match(cfg_arr[1])[1 .. -1][0]
	dirlist = /"([^"]*)/.match(cfg_arr[2])[1 .. -1][0]
	postfix = /"([^"]*)/.match(cfg_arr[3])[1 .. -1][0]
	log_path = /"([^"]*)/.match(cfg_arr[4])[1 .. -1][0]
	contact_list = /"([^"]*)/.match(cfg_arr[8])[1 .. -1][0]
	
	# Convert csv string from config file to an array of strings
	dirs = Array.new()
	dirlist.split(', ').each do |dir|
		dirs.push(dir.strip)
	end

	# Construct list of paths to fixi.db (dotpaths)
	dotpaths = Array.new()
	dirs.each do |dir|
		paths = "#{prefix}/#{dir}"
		dotpaths.push(paths)
	end

	contacts_arr = Array.new()
	contact_list.split(', ').each do |c|
		contacts_arr.push(c.strip)
	end

	# Associative arrays for e-mail contacts
	contacts = {}
	bcc_contacts = {}

	dirs.zip(contacts_arr).each do |dir, contact|
		contacts[dir] = contact
		if contacts[dir] != bcc_contact
			bcc_contacts[dir] = bcc_contact
		else
			bcc_contacts[dir] = ""
		end
		#puts "#{dir} => to: #{contacts[dir]}, bcc: #{bcc_contacts[dir]}"
	end
	
  # Checking entire Darklib takes between 7-8 full days so we want to look back about 9 days for completed audits. 
  # This should increase the chance that there is at least one complete audit for each site when the email reports go out.
  # 9 days * 24 hrs/day * 60 min/hr * 60 seconds/min 
  SECONDS_PER_WEEK_CONST = 777600
	timefstr = "%a %b %d %H:%M:%S %Y"
  t = Time.now	
	
  utime_now = t.to_i
	time_now_str = t.strftime(timefstr)
	time_now_subj = t.strftime("%D")

	utime_week_prior = utime_now - SECONDS_PER_WEEK_CONST
	time_week_prior_str = Time.at(utime_week_prior).to_datetime.strftime(timefstr) 
	time_week_prior_subj = Time.at(utime_week_prior).to_datetime.strftime("%D")
	
	# For each directory	
	dirs.each_with_index do |dir, index|	
		if Dir.exists?(dotpaths[index])
			
			old_stdout = $stdout.dup	
			log_fh = File.new('/tmp/report.txt', "w")
			$stdout = log_fh
			
			puts "\nFrom: #{time_week_prior_str}"
			puts "To: #{time_now_str}"

			db = SQLite3::Database.new(File.join(dotpaths[index], "/#{postfix}"))
			db.busy_timeout = 600000
		
			# Get uuid of last run. Will include it in the count only if finished.
			last_rowid = db.get_first_value("SELECT rowid,* FROM dates ORDER BY rowid DESC LIMIT(1)");
      
      # Determine if any runs were finished in the last week
      rowCount = db.get_first_value("SELECT count(*) FROM dates WHERE (finished == 1 and utime >= #{utime_week_prior});")

			# Get all runs between now and a week ago.
			sql = "select rowid,* from dates where (utime >= #{utime_week_prior});"
			rows = db.execute(sql)
			attrCounts = {
					"A" => 0,
					"D" => 0,
					"M" => 0,
			}
			report_str = ""
			# For each uuid
			rows.each do |row| 
				rowid = row[0]	
				uuid = row[1]
				directoryname = row[2]
				starttime = row[3]
				finished = row[4]
				
				# Skip uuid if most recent & not finished
				if (rowid == last_rowid) && (finished == 0)
					next
				end
				
				adds = db.get_first_value( "SELECT COUNT(*) FROM audit WHERE uuid=? AND attr=?", [uuid, 'A'])				
				attrCounts['A'] +=  adds
				deletes = db.get_first_value( "SELECT COUNT(*) FROM audit WHERE uuid=? AND attr=?", [uuid, 'D'])				
				attrCounts['D'] +=  deletes
				modifies = db.get_first_value( "SELECT COUNT(*) FROM audit WHERE uuid=? AND attr=?", [uuid, 'M'])				
				attrCounts['M'] += modifies 
				
        report_str += "<p><b>#{Time.at(starttime).to_datetime.strftime(timefstr)}</b><br>"
				report_str += "UUID: #{uuid}<br>Add: #{adds}<br>Delete: #{deletes}<br>Modify: #{modifies}"
				
				puts ""
				puts "#{Time.at(starttime).to_datetime.strftime(timefstr)}"
				puts "UUID: #{uuid}"
				puts "Added: #{adds}" 
				puts "Deleted: #{deletes}"
				puts "Modified: #{modifies}" 
				
			end

      if rowCount == 0 
        lastAudit = db.get_first_row("SELECT rowid,* FROM dates WHERE (finished == 1) ORDER BY rowid DESC LIMIT(1)")
        uuid = lastAudit[1]
        starttime = lastAudit[3]

        adds = db.get_first_value( "SELECT COUNT(*) FROM audit WHERE uuid=? AND attr=?", [uuid, 'A'])				
				attrCounts['A'] +=  adds
				deletes = db.get_first_value( "SELECT COUNT(*) FROM audit WHERE uuid=? AND attr=?", [uuid, 'D'])				
				attrCounts['D'] +=  deletes
				modifies = db.get_first_value( "SELECT COUNT(*) FROM audit WHERE uuid=? AND attr=?", [uuid, 'M'])				
				attrCounts['M'] += modifies 
        
        report_str += "<p><b>No audits completed in the past week.<br>"
        report_str += "Last completed audit:</b><br><br>"
        report_str += "<b>#{Time.at(starttime).to_datetime.strftime(timefstr)}</b><br>"
				report_str += "UUID: #{uuid}<br>Add: #{adds}<br>Delete: #{deletes}<br>Modify: #{modifies}"

        puts ""
        puts "No audits completed in the past week."
        puts "Last completed audit:" 
        puts ""
				puts "#{Time.at(starttime).to_datetime.strftime(timefstr)}"
				puts "UUID: #{uuid}"
				puts "Added: #{adds}" 
				puts "Deleted: #{deletes}"
				puts "Modified: #{modifies}" 
      end

			puts "\nWeekly totals:"
			puts "Added: #{attrCounts['A']}"
			puts "Deleted: #{attrCounts['D']}"
			puts "Modified: #{attrCounts['M']}"
		
			$stdout = old_stdout
			log_fh.close

			Mail.deliver do 
				from 'fixity@example.org'
				to "#{contacts[dir]}"
        cc "#{cc_contacts[dir]}"
        bcc	"#{bcc_contacts[dir]}"
				subject "Fixity for #{dir} (#{time_now_subj})"
				
				text_part do
					body File.read('/tmp/report.txt')
				end

				html_part do
					content_type 'text/html; charset=UTF-8'
					body "
						<p>From: #{time_week_prior_str}<br>To: #{time_now_str}</p>
						<p><b>Summary:</b><br>
						Add: #{attrCounts['A']}<br>
						Delete: #{attrCounts['D']}<br>
						Modify: #{attrCounts['M']}</p>
						#{report_str}"
        end
      end
    end
  end
end

# vim: set ts=2 sw=2 :
