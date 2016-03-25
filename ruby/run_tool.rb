require 'rake'
require 'rubocop/rake_task'

if ARGV.empty?
    raise 'Project directory must be provided as argument!'
end

project_dir = ARGV[0];

chdir(project_dir);

rake = Rake.application
rake.init
rake.load_rakefile

patterns = []
rakefile = File.open("Rakefile", "rb").read
scanning_rubocop = false
rakefile.each_line do |line|
    if line.start_with? "RuboCop"
        scanning_rubocop = true
    end

    if scanning_rubocop and line.include? ".patterns"
        parts = /\[(.+)\]/.match line
        patterns = parts[1].split(",").map{|s| s.strip.tr('\'"', '')}
    end

    if line.start_with? "end" and scanning_rubocop
        scanning_rubocop = false
    end
end

RuboCop::RakeTask.new(:rubocop_formatted) do |t|
    t.patterns = patterns
    t.formatters = ['json']
    t.fail_on_error = false
end

rake['rubocop_formatted'].invoke